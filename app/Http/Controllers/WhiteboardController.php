<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Whiteboard;
use App\Models\WhiteboardShare;
use App\Notifications\WhiteboardSharedNotification;
use Illuminate\Http\Request;

class WhiteboardController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('view_whiteboards'), 403);
        $viewingAll = auth()->user()->hasPermission('view_all_whiteboards');
        $validTabs  = $viewingAll ? ['mine', 'shared', 'all'] : ['mine', 'shared'];
        $tab        = in_array($request->get('tab'), $validTabs) ? $request->get('tab') : 'mine';
        $search     = $request->get('search', '');

        $myCount     = Whiteboard::where('user_id', auth()->id())->count();
        $sharedCount = Whiteboard::whereHas('shares', fn ($q) => $q->where('shared_with_user_id', auth()->id()))->count();
        $allCount    = $viewingAll ? Whiteboard::count() : 0;

        $boards = match ($tab) {
            'shared' => Whiteboard::whereHas('shares', fn ($q) => $q->where('shared_with_user_id', auth()->id()))
                ->with('user')->withCount('shares')
                ->when($search, fn ($q) => $q->where('title', 'like', "%{$search}%"))
                ->latest()->get(),
            'all' => Whiteboard::with('user')->withCount('shares')
                ->when($search, fn ($q) => $q->where('title', 'like', "%{$search}%"))
                ->when($request->filled('owner'), fn ($q) => $q->where('user_id', $request->owner))
                ->latest()->get(),
            default => Whiteboard::where('user_id', auth()->id())->withCount('shares')
                ->when($search, fn ($q) => $q->where('title', 'like', "%{$search}%"))
                ->latest()->get(),
        };

        $users = User::orderBy('name')->get();

        return view('whiteboards.index', compact('boards', 'myCount', 'sharedCount', 'allCount', 'users', 'viewingAll', 'tab'));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('create_whiteboards'), 403);
        $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
        ]);

        $title = trim($request->input('title', ''));

        if ($title === '') {
            $count = Whiteboard::where('user_id', auth()->id())
                ->where('title', 'like', 'Untitled_%')
                ->count();
            $title = 'Untitled_' . ($count + 1);
        }

        $whiteboard = Whiteboard::create([
            'user_id' => auth()->id(),
            'title'   => $title,
        ]);

        return redirect()->route('whiteboards.show', $whiteboard);
    }

    public function show(Whiteboard $whiteboard)
    {
        $canOversee = auth()->user()->hasPermission('view_all_whiteboards');
        $isOwner    = $whiteboard->user_id === auth()->id();
        $isShared   = $whiteboard->shares()->where('shared_with_user_id', auth()->id())->exists();

        abort_unless($isOwner || $canOversee || $isShared, 403);

        $canEdit = $isOwner || $isShared;

        $whiteboard->load('user', 'shares.sharedWith');
        $users = User::where('id', '!=', auth()->id())->orderBy('name')->get();

        $elementsPath = public_path("wb/{$whiteboard->id}_elements.json");
        $elementsData = file_exists($elementsPath)
            ? json_decode(file_get_contents($elementsPath), true)
            : [];

        return view('whiteboards.show', compact('whiteboard', 'isOwner', 'canEdit', 'users', 'elementsData'));
    }

    public function rename(Request $request, Whiteboard $whiteboard)
    {
        abort_unless($whiteboard->user_id === auth()->id(), 403);

        $request->validate(['title' => ['required', 'string', 'max:255']]);

        $whiteboard->update(['title' => trim($request->input('title'))]);

        return response()->json(['success' => true, 'title' => $whiteboard->title]);
    }

    public function save(Request $request, Whiteboard $whiteboard)
    {
        $isShared = $whiteboard->shares()->where('shared_with_user_id', auth()->id())->exists();
        abort_unless($whiteboard->user_id === auth()->id() || $isShared, 403);

        $request->validate([
            'canvas_data'   => ['nullable', 'string'],
            'elements_data' => ['nullable', 'array'],
        ]);

        $dir = public_path('wb');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        if ($request->filled('canvas_data')) {
            $base64    = preg_replace('/^data:image\/\w+;base64,/', '', $request->input('canvas_data'));
            $imageData = base64_decode($base64);
            $filename  = $whiteboard->id . '.png';
            file_put_contents($dir . '/' . $filename, $imageData);
            $whiteboard->update(['file_path' => $filename]);
        }

        if ($request->has('elements_data')) {
            file_put_contents(
                $dir . '/' . $whiteboard->id . '_elements.json',
                json_encode($request->input('elements_data', []))
            );
        }

        return response()->json(['success' => true]);
    }

    public function attach(Request $request, Whiteboard $whiteboard)
    {
        $isShared = $whiteboard->shares()->where('shared_with_user_id', auth()->id())->exists();
        abort_unless($whiteboard->user_id === auth()->id() || $isShared, 403);

        $request->validate([
            'file' => ['required', 'file', 'max:51200'],
        ]);

        $uploaded     = $request->file('file');
        $size         = $uploaded->getSize();
        $mimeType     = $uploaded->getMimeType();
        $originalName = $uploaded->getClientOriginalName();
        $extension    = $uploaded->getClientOriginalExtension();
        $baseName     = \Illuminate\Support\Str::slug(pathinfo($originalName, PATHINFO_FILENAME));
        $filename     = time() . '_' . $baseName . '.' . $extension;

        $dir = public_path("wb/attachments/{$whiteboard->id}");
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $uploaded->move($dir, $filename);

        return response()->json([
            'url'      => asset("wb/attachments/{$whiteboard->id}/{$filename}"),
            'filename' => $originalName,
            'size'     => $size,
            'mime'     => $mimeType,
        ]);
    }

    public function destroy(Whiteboard $whiteboard)
    {
        abort_unless($whiteboard->user_id === auth()->id(), 403);

        if ($whiteboard->file_path) {
            $path = public_path('wb/' . $whiteboard->file_path);
            if (file_exists($path)) {
                unlink($path);
            }
        }

        $whiteboard->delete();

        return back()->with('success', 'Whiteboard deleted.');
    }

    public function share(Request $request, Whiteboard $whiteboard)
    {
        abort_unless($whiteboard->user_id === auth()->id(), 403);

        $data = $request->validate([
            'user_ids'   => ['required', 'array'],
            'user_ids.*' => ['exists:users,id'],
        ]);

        $sharer = auth()->user();

        foreach ($data['user_ids'] as $userId) {
            $share = WhiteboardShare::firstOrCreate(
                ['whiteboard_id' => $whiteboard->id, 'shared_with_user_id' => $userId],
                ['shared_by_user_id' => $sharer->id]
            );

            if ($share->wasRecentlyCreated) {
                User::find($userId)?->notify(new WhiteboardSharedNotification($whiteboard, $sharer));
            }
        }

        return back()->with('success', 'Whiteboard shared.');
    }

    public function unshare(Whiteboard $whiteboard, WhiteboardShare $share)
    {
        abort_unless($whiteboard->user_id === auth()->id(), 403);

        $share->delete();

        return back()->with('success', 'Access removed.');
    }
}

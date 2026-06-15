<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\User;
use App\Models\UserAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('view_clients'), 403);
        $query = Client::with('user');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('company_name', 'like', "%{$search}%")
                  ->orWhere('industry', 'like', "%{$search}%");
            });
        }

        $clients = $query->orderBy('company_name')->paginate(15)->withQueryString();

        return view('clients.index', compact('clients'));
    }

    public function show(Client $client)
    {
        abort_unless(auth()->user()->hasPermission('view_clients'), 403);
        $client->load('user.attachments', 'projects');

        return view('clients.show', compact('client'));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('create_clients'), 403);
        $validated = $request->validate([
            'name'              => ['required', 'string', 'max:255'],
            'email'             => ['required', 'email', 'unique:users,email'],
            'password'          => ['required', 'string', 'min:8'],
            'status'            => ['required', 'in:active,inactive,suspended'],
            'profile_image'     => ['nullable', 'image', 'max:2048'],
            'company_name'      => ['required', 'string', 'max:255'],
            'company_website'   => ['nullable', 'url', 'max:500'],
            'industry'          => ['nullable', 'string', 'max:255'],
            'notes'             => ['nullable', 'string'],
            'attachments'       => ['nullable', 'array', 'max:20'],
            'attachments.*.key' => ['nullable', 'string', 'max:255'],
            'attachments.*.file'=> ['nullable', 'file', 'max:10240'],
        ]);

        $profileImage = $request->hasFile('profile_image')
            ? $request->file('profile_image')->store('profile-images', 'public')
            : $this->generateAvatar($validated['name']);

        $user = User::create([
            'name'                 => $validated['name'],
            'email'                => $validated['email'],
            'password'             => $validated['password'],
            'status'               => $validated['status'],
            'profile_image'        => $profileImage,
            'must_change_password' => true,
        ]);

        $client = Client::create([
            'user_id'         => $user->id,
            'company_name'    => $validated['company_name'],
            'company_website' => $validated['company_website'] ?? null,
            'industry'        => $validated['industry'] ?? null,
            'notes'           => $validated['notes'] ?? null,
        ]);

        $this->storeAttachments(
            $request->input('attachments', []),
            $request->file('attachments', []),
            $user,
            'client-attachments'
        );

        return redirect()->route('clients.show', $client)
            ->with('success', 'Client created successfully.');
    }

    public function update(Request $request, Client $client)
    {
        abort_unless(auth()->user()->hasPermission('edit_clients'), 403);
        $validated = $request->validate([
            'name'            => ['required', 'string', 'max:255'],
            'email'           => ['required', 'email', 'unique:users,email,' . $client->user_id],
            'status'          => ['required', 'in:active,inactive,suspended'],
            'profile_image'   => ['nullable', 'image', 'max:2048'],
            'company_name'    => ['required', 'string', 'max:255'],
            'company_website' => ['nullable', 'url', 'max:500'],
            'industry'        => ['nullable', 'string', 'max:255'],
            'notes'           => ['nullable', 'string'],
        ]);

        $userUpdate = [
            'name'   => $validated['name'],
            'email'  => $validated['email'],
            'status' => $validated['status'],
        ];

        if ($request->hasFile('profile_image')) {
            if ($client->user->profile_image) {
                Storage::disk('public')->delete($client->user->profile_image);
            }
            $userUpdate['profile_image'] = $request->file('profile_image')->store('profile-images', 'public');
        }

        $client->user->update($userUpdate);

        $client->update([
            'company_name'    => $validated['company_name'],
            'company_website' => $validated['company_website'] ?? null,
            'industry'        => $validated['industry'] ?? null,
            'notes'           => $validated['notes'] ?? null,
        ]);

        return redirect()->route('clients.show', $client)
            ->with('success', 'Client updated successfully.');
    }

    public function destroy(Client $client)
    {
        abort_unless(auth()->user()->hasPermission('delete_clients'), 403);
        $client->delete();

        return redirect()->route('clients.index')
            ->with('success', 'Client deleted successfully.');
    }

    public function storeAttachment(Request $request, Client $client)
    {
        abort_unless(auth()->user()->hasPermission('edit_clients'), 403);
        $request->validate([
            'key'  => ['required', 'string', 'max:255'],
            'file' => ['required', 'file', 'max:10240'],
        ]);

        $path = $request->file('file')->store(
            'client-attachments/' . $client->user_id,
            'public'
        );

        $client->user->attachments()->create([
            'key'             => $request->key,
            'attachment_path' => $path,
        ]);

        return back()->with('success', 'Attachment uploaded.');
    }

    public function destroyAttachment(Client $client, UserAttachment $userAttachment)
    {
        abort_unless(auth()->user()->hasPermission('edit_clients'), 403);
        abort_if($userAttachment->user_id !== $client->user_id, 403);

        Storage::disk('public')->delete($userAttachment->attachment_path);
        $userAttachment->delete();

        return back()->with('success', 'Attachment deleted.');
    }
}

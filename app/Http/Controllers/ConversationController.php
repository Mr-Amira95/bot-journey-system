<?php

namespace App\Http\Controllers;

use App\Enums\ConversationType;
use App\Enums\MessageType;
use App\Events\MessageSent;
use App\Models\Conversation;
use App\Models\ConversationUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ConversationController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('view_conversations'), 403);
        $userId     = auth()->id();
        $viewingAll = auth()->user()->hasPermission('view_all_messages');
        $tab        = ($viewingAll && $request->get('tab') === 'all') ? 'all' : 'mine';

        $query = Conversation::with(['latestMessage.sender', 'users'])
            ->withCount(['messages as unread_count' => function ($q) use ($userId) {
                $q->where('is_read', false)->where('user_id', '!=', $userId);
            }])
            ->latest('updated_at');

        if ($tab === 'mine') {
            $query->whereHas('members', function ($q) use ($userId) {
                $q->where('user_id', $userId)->whereNull('left_at');
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhereHas('users', fn ($u) => $u->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($tab === 'all' && $request->filled('participant')) {
            $query->whereHas('members', fn ($q) => $q->where('user_id', $request->participant));
        }

        $conversations = $query->get();
        $users         = User::where('id', '!=', $userId)->orderBy('name')->get();

        return view('conversations.index', compact('conversations', 'users', 'viewingAll', 'tab'));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('create_conversations'), 403);
        $data = $request->validate([
            'type'        => ['required', Rule::enum(ConversationType::class)],
            'title'       => ['required_if:type,group', 'nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'user_ids'    => ['required', 'array', 'min:1'],
            'user_ids.*'  => ['exists:users,id'],
        ]);

        if ($data['type'] === 'direct' && count($data['user_ids']) === 1) {
            $existing = $this->findDirectConversation(auth()->id(), (int) $data['user_ids'][0]);
            if ($existing) {
                return redirect()->route('conversations.show', $existing);
            }
        }

        $conversation = Conversation::create([
            'type'        => $data['type'],
            'title'       => $data['title'] ?? null,
            'description' => $data['description'] ?? null,
            'created_by'  => auth()->id(),
        ]);

        $conversation->members()->create([
            'user_id' => auth()->id(),
            'role'    => 'admin',
        ]);

        foreach ($data['user_ids'] as $userId) {
            $conversation->members()->create([
                'user_id' => $userId,
                'role'    => 'member',
            ]);
        }

        return redirect()->route('conversations.show', $conversation);
    }

    public function show(Conversation $conversation)
    {
        $userId      = auth()->id();
        $canOversee  = auth()->user()->hasPermission('view_all_messages');

        $isMember = $conversation->members()
            ->where('user_id', $userId)
            ->whereNull('left_at')
            ->exists();

        abort_unless($isMember || $canOversee, 403);

        $oversightMode = $canOversee && ! $isMember;

        $messages = $conversation->messages()
            ->with(['sender', 'replyTo.sender', 'attachments'])
            ->oldest()
            ->get();

        $conversation->load('users', 'members.user');

        $myMembership = $conversation->members()->where('user_id', $userId)->first();

        return view('conversations.show', compact('conversation', 'messages', 'myMembership', 'oversightMode'));
    }

    public function sendMessage(Request $request, Conversation $conversation)
    {
        $userId = auth()->id();

        $isMember = $conversation->members()
            ->where('user_id', $userId)
            ->whereNull('left_at')
            ->exists();

        abort_unless($isMember, 403);

        $data = $request->validate([
            'body'     => ['required', 'string', 'max:5000'],
            'reply_to' => ['nullable', 'exists:messages,id'],
        ]);

        $message = $conversation->messages()->create([
            'user_id'  => $userId,
            'type'     => MessageType::Text,
            'body'     => $data['body'],
            'reply_to' => $data['reply_to'] ?? null,
        ]);

        $message->load('sender', 'replyTo.sender');

        $conversation->touch();

        broadcast(new MessageSent($message))->toOthers();

        return response()->json([
            'success' => true,
            'message' => [
                'id'         => $message->id,
                'user_id'    => $message->user_id,
                'type'       => $message->type->value,
                'body'       => $message->body,
                'reply_to'   => $message->reply_to,
                'reply_body' => $message->replyTo?->body,
                'reaction'   => $message->reaction,
                'created_at' => $message->created_at->format('H:i'),
                'sender'     => [
                    'id'   => $message->sender->id,
                    'name' => $message->sender->name,
                ],
            ],
        ]);
    }

    public function react(Request $request, Conversation $conversation, \App\Models\Message $message)
    {
        $isMember = $conversation->members()
            ->where('user_id', auth()->id())
            ->whereNull('left_at')
            ->exists();

        abort_unless($isMember, 403);

        $data = $request->validate(['reaction' => ['nullable', 'string', 'max:10']]);

        $newReaction = $message->reaction === $data['reaction'] ? null : ($data['reaction'] ?? null);
        $message->update(['reaction' => $newReaction]);

        return response()->json(['success' => true, 'reaction' => $newReaction]);
    }

    public function destroy(Conversation $conversation)
    {
        abort_unless($conversation->created_by === auth()->id(), 403);

        $conversation->delete();

        return redirect()->route('conversations.index')->with('success', 'Conversation deleted.');
    }

    private function findDirectConversation(int $userId1, int $userId2): ?Conversation
    {
        return Conversation::where('type', 'direct')
            ->whereHas('members', fn ($q) => $q->where('user_id', $userId1))
            ->whereHas('members', fn ($q) => $q->where('user_id', $userId2))
            ->first();
    }
}

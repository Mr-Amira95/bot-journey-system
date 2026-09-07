<?php

namespace App\Http\Controllers;

use App\Enums\ConversationType;
use App\Enums\MessageType;
use App\Events\MessageSent;
use App\Models\Conversation;
use App\Models\ConversationUser;
use App\Models\Message;
use App\Models\MessageAttachment;
use App\Models\User;
use App\Notifications\NewMessageNotification;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ConversationController extends Controller
{
    private const ATTACHMENT_MIMES = 'jpg,jpeg,png,gif,webp,mp4,mov,avi,webm,pdf,doc,docx,xls,xlsx,ppt,pptx,txt,zip,mp3,wav,m4a,ogg,aac';

    public function index(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('view_conversations'), 403);
        $userId     = auth()->id();
        $viewingAll = auth()->user()->hasPermission('view_all_messages');
        $tab        = ($viewingAll && $request->get('tab') === 'all') ? 'all' : 'mine';

        $query = Conversation::with(['latestMessage.sender', 'users'])
            ->withCount(['messages as unread_count' => function ($q) use ($userId) {
                $q->where('user_id', '!=', $userId)
                  ->whereRaw('messages.id > (select coalesce(last_read_message_id, 0) from conversation_users where conversation_users.conversation_id = messages.conversation_id and conversation_users.user_id = ?)', [$userId]);
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

        if ($isMember && $myMembership && $messages->isNotEmpty()) {
            $myMembership->update(['last_read_message_id' => $messages->last()->id]);
        }

        return view('conversations.show', compact('conversation', 'messages', 'myMembership', 'oversightMode'));
    }

    public function markRead(Conversation $conversation)
    {
        $userId     = auth()->id();
        $membership = $conversation->members()->where('user_id', $userId)->whereNull('left_at')->first();

        abort_unless($membership, 403);

        $lastMessageId = $conversation->messages()->max('id');
        if ($lastMessageId) {
            $membership->update(['last_read_message_id' => $lastMessageId]);
        }

        return response()->json(['success' => true]);
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
            'body'           => ['nullable', 'string', 'max:5000', 'required_without_all:attachments,voice'],
            'reply_to'       => ['nullable', 'exists:messages,id'],
            'attachments'    => ['nullable', 'array'],
            'attachments.*'  => ['file', 'max:20480', 'mimes:' . self::ATTACHMENT_MIMES],
            'voice'          => ['nullable', 'file', 'max:20480', 'mimes:' . self::ATTACHMENT_MIMES],
            'voice_duration' => ['nullable', 'integer', 'min:0'],
        ]);

        $attachmentFiles = $request->file('attachments', []);
        $voiceFile       = $request->file('voice');

        $message = $conversation->messages()->create([
            'user_id'  => $userId,
            'type'     => $this->resolveMessageType($data['body'] ?? null, $attachmentFiles, $voiceFile),
            'body'     => $data['body'] ?? null,
            'reply_to' => $data['reply_to'] ?? null,
        ]);

        foreach ($attachmentFiles as $file) {
            $this->createMessageAttachment($message, $file);
        }

        if ($voiceFile) {
            $this->createMessageAttachment($message, $voiceFile, 'voice', $data['voice_duration'] ?? null);
        }

        $message->load('sender', 'replyTo.sender', 'attachments');

        $conversation->touch();

        broadcast(new MessageSent($message))->toOthers();

        $recipients = $conversation->members()
            ->where('user_id', '!=', $userId)
            ->whereNull('left_at')
            ->whereNull('muted_at')
            ->with('user')
            ->get()
            ->pluck('user')
            ->filter();

        foreach ($recipients as $recipient) {
            $recipient->notify(new NewMessageNotification($conversation, $message, $message->sender));
        }

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
                'attachments' => $message->attachments->map(fn ($a) => $this->attachmentPayload($a))->values(),
                'created_at' => $message->created_at->format('H:i'),
                'sender'     => [
                    'id'   => $message->sender->id,
                    'name' => $message->sender->name,
                ],
            ],
        ]);
    }

    public function react(Request $request, Conversation $conversation, Message $message)
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

    /**
     * @param  UploadedFile[]  $attachmentFiles
     */
    private function resolveMessageType(?string $body, array $attachmentFiles, ?UploadedFile $voiceFile): MessageType
    {
        if ($voiceFile) {
            return MessageType::Voice;
        }

        if (! empty($body)) {
            return MessageType::Text;
        }

        if (count($attachmentFiles) === 1) {
            return match ($this->attachmentType($attachmentFiles[0]->getClientOriginalExtension())) {
                'image' => MessageType::Image,
                'video' => MessageType::Video,
                'audio' => MessageType::Audio,
                default => MessageType::File,
            };
        }

        return MessageType::File;
    }

    private function createMessageAttachment(Message $message, UploadedFile $file, ?string $forceType = null, ?int $duration = null): MessageAttachment
    {
        $folder = 'conversation-attachments/' . $message->conversation_id . '/' . $message->id;
        $path   = $file->store($folder, 'public');

        return $message->attachments()->create([
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_type' => $forceType ?? $this->attachmentType($file->getClientOriginalExtension()),
            'mime_type' => $file->getMimeType(),
            'size'      => $file->getSize(),
            'duration'  => $duration,
        ]);
    }

    private function attachmentType(string $extension): string
    {
        $extension = strtolower($extension);

        return match (true) {
            in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true) => 'image',
            in_array($extension, ['mp4', 'mov', 'avi', 'webm'], true)        => 'video',
            in_array($extension, ['mp3', 'wav', 'm4a', 'ogg', 'aac'], true)  => 'audio',
            $extension === 'pdf'                                             => 'pdf',
            in_array($extension, ['doc', 'docx'], true)                      => 'word',
            in_array($extension, ['xls', 'xlsx'], true)                      => 'excel',
            in_array($extension, ['ppt', 'pptx'], true)                      => 'powerpoint',
            default                                                          => 'file',
        };
    }

    private function attachmentPayload(MessageAttachment $attachment): array
    {
        return [
            'id'        => $attachment->id,
            'file_name' => $attachment->file_name,
            'file_type' => $attachment->file_type,
            'mime_type' => $attachment->mime_type,
            'size'      => $attachment->size,
            'duration'  => $attachment->duration,
            'url'       => Storage::disk('public')->url($attachment->file_path),
        ];
    }
}

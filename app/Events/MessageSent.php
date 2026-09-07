<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Message $message) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('conversation.' . $this->message->conversation_id)];
    }

    public function broadcastAs(): string
    {
        return 'message.sent';
    }

    public function broadcastWith(): array
    {
        return [
            'id'              => $this->message->id,
            'conversation_id' => $this->message->conversation_id,
            'user_id'         => $this->message->user_id,
            'type'            => $this->message->type->value,
            'body'            => $this->message->body,
            'reply_to'        => $this->message->reply_to,
            'reaction'        => $this->message->reaction,
            'attachments'     => $this->message->attachments->map(fn ($a) => [
                'id'        => $a->id,
                'file_name' => $a->file_name,
                'file_type' => $a->file_type,
                'mime_type' => $a->mime_type,
                'size'      => $a->size,
                'duration'  => $a->duration,
                'url'       => \Illuminate\Support\Facades\Storage::disk('public')->url($a->file_path),
            ])->values(),
            'created_at'      => $this->message->created_at->toISOString(),
            'sender'          => [
                'id'   => $this->message->sender->id,
                'name' => $this->message->sender->name,
            ],
        ];
    }
}

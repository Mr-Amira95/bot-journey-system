<?php

namespace App\Notifications;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;

class NewMessageNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Conversation $conversation,
        public readonly Message $message,
        public readonly User $sender,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast', 'mail'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'            => 'new_message',
            'title'           => "New message from {$this->sender->name}",
            'body'            => \Illuminate\Support\Str::limit($this->message->body, 200),
            'url'             => route('conversations.show', $this->conversation),
            'conversation_id' => $this->conversation->id,
            'message_id'      => $this->message->id,
            'sender'          => $this->sender->name,
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("New message from {$this->sender->name}")
            ->view('emails.new-message', [
                'notifiable'   => $notifiable,
                'conversation' => $this->conversation,
                'message'      => $this->message,
                'sender'       => $this->sender,
                'conversationUrl' => route('conversations.show', $this->conversation),
            ]);
    }
}

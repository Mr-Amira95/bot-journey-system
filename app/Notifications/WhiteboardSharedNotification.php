<?php

namespace App\Notifications;

use App\Models\User;
use App\Models\Whiteboard;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;

class WhiteboardSharedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Whiteboard $whiteboard,
        public readonly User $sharedBy,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast', 'mail'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'          => 'whiteboard_shared',
            'title'         => 'Whiteboard shared with you',
            'body'          => "{$this->sharedBy->name} shared \"{$this->whiteboard->title}\" with you.",
            'url'           => route('whiteboards.show', $this->whiteboard),
            'whiteboard_id' => $this->whiteboard->id,
            'board_title'   => $this->whiteboard->title,
            'shared_by'     => $this->sharedBy->name,
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("{$this->sharedBy->name} shared a whiteboard with you")
            ->view('emails.whiteboard-shared', [
                'notifiable'  => $notifiable,
                'whiteboard'  => $this->whiteboard,
                'sharedBy'    => $this->sharedBy,
                'boardUrl'    => route('whiteboards.show', $this->whiteboard),
            ]);
    }
}

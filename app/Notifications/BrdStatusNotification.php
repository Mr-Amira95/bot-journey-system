<?php

namespace App\Notifications;

use App\Models\Brd;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;

class BrdStatusNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Brd $brd,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast', 'mail'];
    }

    public function toArray(object $notifiable): array
    {
        $status = $this->brd->status->value ?? $this->brd->status;
        $label  = ucfirst($status);

        return [
            'type'    => 'brd_status',
            'title'   => "BRD {$label}",
            'body'    => "Your BRD \"{$this->brd->title}\" has been {$status}.",
            'url'     => route('brds.index'),
            'brd_id'    => $this->brd->id,
            'brd_title' => $this->brd->title,
            'status'    => $status,
            'project' => $this->brd->project?->name,
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }

    public function toMail(object $notifiable): MailMessage
    {
        $status = $this->brd->status->value ?? $this->brd->status;
        $label  = ucfirst($status);

        return (new MailMessage)
            ->subject("BRD {$label}: {$this->brd->title}")
            ->view('emails.brd-status', [
                'notifiable' => $notifiable,
                'brd'        => $this->brd,
                'status'     => $status,
                'label'      => $label,
                'listUrl'    => route('brds.index'),
            ]);
    }
}

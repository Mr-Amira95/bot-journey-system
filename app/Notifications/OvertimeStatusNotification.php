<?php

namespace App\Notifications;

use App\Models\OvertimeRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class OvertimeStatusNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly OvertimeRequest $overtimeRequest,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray(object $notifiable): array
    {
        $status = $this->overtimeRequest->status->value ?? $this->overtimeRequest->status;
        $label  = ucfirst($status);

        return [
            'type'        => 'overtime_status',
            'title'       => "Overtime request {$label}",
            'body'        => "Your overtime request for {$this->overtimeRequest->hours}h on {$this->overtimeRequest->date?->format('M d, Y')} has been {$status}.",
            'url'         => route('overtime-requests.index'),
            'overtime_id' => $this->overtimeRequest->id,
            'hours'       => $this->overtimeRequest->hours,
            'date'        => $this->overtimeRequest->date?->toDateString(),
            'status'      => $status,
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}

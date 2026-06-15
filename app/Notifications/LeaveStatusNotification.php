<?php

namespace App\Notifications;

use App\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class LeaveStatusNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly LeaveRequest $leaveRequest,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray(object $notifiable): array
    {
        $status = $this->leaveRequest->status->value ?? $this->leaveRequest->status;
        $label  = ucfirst($status);

        return [
            'type'          => 'leave_status',
            'title'         => "Leave request {$label}",
            'body'          => "Your {$this->leaveRequest->leaveType?->name} leave request has been {$status}.",
            'url'           => route('leave-requests.index'),
            'leave_id'      => $this->leaveRequest->id,
            'leave_type'    => $this->leaveRequest->leaveType?->name,
            'status'        => $status,
            'start_date'    => $this->leaveRequest->start_date?->toDateString(),
            'end_date'      => $this->leaveRequest->end_date?->toDateString(),
            'total_days'    => $this->leaveRequest->total_days,
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}

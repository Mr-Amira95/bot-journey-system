<?php

namespace App\Notifications;

use App\Models\Task;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class TaskAssignedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Task $task,
        public readonly User $assignedBy,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'         => 'task_assigned',
            'title'        => 'Task assigned to you',
            'body'         => "\"{$this->task->title}\" was assigned to you by {$this->assignedBy->name}",
            'url'          => route('tasks.index'),
            'task_id'      => $this->task->id,
            'task_title'   => $this->task->title,
            'project_name' => $this->task->project?->name,
            'assigned_by'  => $this->assignedBy->name,
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}

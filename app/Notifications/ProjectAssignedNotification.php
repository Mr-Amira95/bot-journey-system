<?php

namespace App\Notifications;

use App\Models\Project;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class ProjectAssignedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Project $project,
        public readonly User $addedBy,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'        => 'project_assigned',
            'title'       => 'Added to a project',
            'body'        => "You were added to \"{$this->project->name}\" by {$this->addedBy->name}",
            'url'         => route('projects.index'),
            'project_id'  => $this->project->id,
            'project_name'=> $this->project->name,
            'added_by'    => $this->addedBy->name,
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}

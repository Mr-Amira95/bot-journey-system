<?php

namespace App\Notifications;

use App\Models\Task;
use App\Models\TaskComment;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;

class TaskCommentNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Task $task,
        public readonly TaskComment $comment,
        public readonly User $commentedBy,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast', 'mail'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'         => 'task_comment',
            'title'        => 'New comment on your task',
            'body'         => "{$this->commentedBy->name} commented on \"{$this->task->title}\"",
            'url'          => route('tasks.show', $this->task),
            'task_id'      => $this->task->id,
            'task_title'   => $this->task->title,
            'comment_id'   => $this->comment->id,
            'comment'      => \Illuminate\Support\Str::limit($this->comment->comment, 200),
            'commented_by' => $this->commentedBy->name,
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("New comment on \"{$this->task->title}\"")
            ->view('emails.task-comment', [
                'notifiable'   => $notifiable,
                'task'         => $this->task,
                'comment'      => $this->comment,
                'commentedBy'  => $this->commentedBy,
                'taskUrl'      => route('tasks.show', $this->task),
            ]);
    }
}

<?php

namespace App\Notifications;

use App\Models\Expense;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class ExpenseStatusNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Expense $expense,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray(object $notifiable): array
    {
        $status = $this->expense->status->value ?? $this->expense->status;
        $label  = ucfirst($status);

        return [
            'type'        => 'expense_status',
            'title'       => "Expense {$label}",
            'body'        => "Your expense \"{$this->expense->title}\" ({$this->expense->amount}) has been {$status}.",
            'url'         => route('expenses.index'),
            'expense_id'  => $this->expense->id,
            'title_val'   => $this->expense->title,
            'amount'      => $this->expense->amount,
            'status'      => $status,
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}

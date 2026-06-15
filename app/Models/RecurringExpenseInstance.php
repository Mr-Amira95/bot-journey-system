<?php

namespace App\Models;

use App\Enums\RecurringInstanceStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecurringExpenseInstance extends Model
{
    use HasFactory;

    protected $fillable = [
        'template_id', 'expense_id', 'due_date', 'status', 'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'status'       => RecurringInstanceStatus::class,
            'due_date'     => 'date',
            'processed_at' => 'datetime',
        ];
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(RecurringExpenseTemplate::class, 'template_id');
    }

    public function expense(): BelongsTo
    {
        return $this->belongsTo(Expense::class);
    }
}

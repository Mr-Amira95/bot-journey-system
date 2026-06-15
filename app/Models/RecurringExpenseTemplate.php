<?php

namespace App\Models;

use App\Enums\RecurringFrequency;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RecurringExpenseTemplate extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title', 'description', 'category_id', 'project_id', 'amount',
        'frequency', 'start_date', 'end_date', 'next_run_date', 'is_active', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'frequency'     => RecurringFrequency::class,
            'amount'        => 'decimal:2',
            'start_date'    => 'date',
            'end_date'      => 'date',
            'next_run_date' => 'date',
            'is_active'     => 'boolean',
            'deleted_at'    => 'datetime',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'category_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function instances(): HasMany
    {
        return $this->hasMany(RecurringExpenseInstance::class, 'template_id');
    }
}

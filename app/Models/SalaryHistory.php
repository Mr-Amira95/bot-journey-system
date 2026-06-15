<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalaryHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id', 'salary', 'hourly_rate',
        'effective_date', 'end_date', 'changed_by', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'salary'         => 'decimal:2',
            'hourly_rate'    => 'decimal:2',
            'effective_date' => 'date',
            'end_date'       => 'date',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}

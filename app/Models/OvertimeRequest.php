<?php

namespace App\Models;

use App\Enums\OvertimeStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OvertimeRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'date',
        'start_time',
        'end_time',
        'hours',
        'multiplier',
        'reason',
        'status',
        'approved_by',
        'approved_at',
        'payroll_item_id',
    ];

    protected function casts(): array
    {
        return [
            'date'        => 'date',
            'hours'       => 'decimal:2',
            'multiplier'  => 'decimal:2',
            'status'      => OvertimeStatus::class,
            'approved_at' => 'datetime',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function payrollItem(): BelongsTo
    {
        return $this->belongsTo(PayrollItem::class);
    }
}

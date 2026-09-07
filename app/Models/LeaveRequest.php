<?php

namespace App\Models;

use App\Enums\LeaveDurationType;
use App\Enums\LeaveRequestStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeaveRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'leave_type_id',
        'duration_type',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'total_days',
        'total_hours',
        'status',
        'reason',
        'approved_by',
        'approved_at',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'duration_type' => LeaveDurationType::class,
            'start_date'    => 'date',
            'end_date'      => 'date',
            'total_days'    => 'decimal:2',
            'total_hours'   => 'decimal:2',
            'status'        => LeaveRequestStatus::class,
            'approved_at'   => 'datetime',
            'deleted_at'    => 'datetime',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}

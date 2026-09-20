<?php

namespace App\Models;

use App\Enums\BrdPriority;
use App\Enums\BrdStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Brd extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'project_id',
        'department',
        'direct_manager',
        'title',
        'description',
        'objective',
        'scope',
        'as_is_workflow',
        'as_is_pain_points',
        'as_is_existing_systems',
        'to_be_workflow',
        'to_be_benefits',
        'kpis',
        'priority',
        'status',
        'created_by',
        'updated_by',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'share_token',
        'ai_canvas_data',
        'ai_canvas_generated_at',
    ];

    protected function casts(): array
    {
        return [
            'status'                 => BrdStatus::class,
            'priority'               => BrdPriority::class,
            'approved_at'            => 'datetime',
            'ai_canvas_data'         => 'array',
            'ai_canvas_generated_at' => 'datetime',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function stakeholders(): HasMany
    {
        return $this->hasMany(BrdStakeholder::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(BrdAttachment::class);
    }
}

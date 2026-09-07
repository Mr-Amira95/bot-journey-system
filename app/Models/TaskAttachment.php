<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskAttachment extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'task_id',
        'comment_id',
        'user_id',
        'file_name',
        'file_path',
        'file_type',
        'file_size',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function comment(): BelongsTo
    {
        return $this->belongsTo(TaskComment::class, 'comment_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

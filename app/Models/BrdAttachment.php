<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BrdAttachment extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'brd_id',
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

    public function brd(): BelongsTo
    {
        return $this->belongsTo(Brd::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

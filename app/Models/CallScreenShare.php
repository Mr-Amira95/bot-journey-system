<?php

namespace App\Models;

use App\Enums\CallScreenShareStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CallScreenShare extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'call_id',
        'user_id',
        'started_at',
        'ended_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status'     => CallScreenShareStatus::class,
            'started_at' => 'datetime',
            'ended_at'   => 'datetime',
        ];
    }

    public function call(): BelongsTo
    {
        return $this->belongsTo(Call::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

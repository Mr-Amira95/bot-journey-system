<?php

namespace App\Models;

use App\Enums\CallParticipantStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CallParticipant extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'call_id',
        'user_id',
        'status',
        'joined_at',
        'left_at',
        'is_muted',
        'is_video_on',
        'is_screen_sharing',
    ];

    protected function casts(): array
    {
        return [
            'status'           => CallParticipantStatus::class,
            'joined_at'        => 'datetime',
            'left_at'          => 'datetime',
            'is_muted'         => 'boolean',
            'is_video_on'      => 'boolean',
            'is_screen_sharing'=> 'boolean',
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

<?php

namespace App\Models;

use App\Enums\CallStatus;
use App\Enums\CallType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Call extends Model
{
    protected $fillable = [
        'conversation_id',
        'type',
        'status',
        'started_at',
        'ended_at',
        'started_by',
        'ended_by',
    ];

    protected function casts(): array
    {
        return [
            'type'       => CallType::class,
            'status'     => CallStatus::class,
            'started_at' => 'datetime',
            'ended_at'   => 'datetime',
        ];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function startedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'started_by');
    }

    public function endedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ended_by');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(CallParticipant::class);
    }

    public function screenShares(): HasMany
    {
        return $this->hasMany(CallScreenShare::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(CallEvent::class);
    }

    public function getDurationAttribute(): ?int
    {
        if ($this->started_at && $this->ended_at) {
            return $this->ended_at->diffInSeconds($this->started_at);
        }
        return null;
    }

    public function getFormattedDurationAttribute(): string
    {
        $secs = $this->duration;
        if ($secs === null) return '—';
        $h = intdiv($secs, 3600);
        $m = intdiv($secs % 3600, 60);
        $s = $secs % 60;
        if ($h > 0) return sprintf('%d:%02d:%02d', $h, $m, $s);
        return sprintf('%02d:%02d', $m, $s);
    }
}

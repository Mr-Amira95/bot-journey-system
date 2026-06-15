<?php

namespace App\Models;

use App\Enums\CallEventType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CallEvent extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'call_id',
        'user_id',
        'event',
        'payload',
    ];

    protected function casts(): array
    {
        return [
            'event'      => CallEventType::class,
            'payload'    => 'array',
            'created_at' => 'datetime',
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

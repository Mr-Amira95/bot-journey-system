<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhiteboardShare extends Model
{
    public $timestamps = false;

    protected $fillable = ['whiteboard_id', 'shared_with_user_id', 'shared_by_user_id'];

    protected function casts(): array
    {
        return ['created_at' => 'datetime'];
    }

    public function whiteboard(): BelongsTo
    {
        return $this->belongsTo(Whiteboard::class);
    }

    public function sharedWith(): BelongsTo
    {
        return $this->belongsTo(User::class, 'shared_with_user_id');
    }

    public function sharedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'shared_by_user_id');
    }
}

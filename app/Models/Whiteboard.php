<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Whiteboard extends Model
{
    use SoftDeletes;

    protected $fillable = ['user_id', 'title', 'file_path'];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function shares(): HasMany
    {
        return $this->hasMany(WhiteboardShare::class);
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        return $this->file_path ? asset('wb/' . $this->file_path) : null;
    }
}

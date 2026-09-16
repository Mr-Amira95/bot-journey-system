<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BrdStakeholder extends Model
{
    protected $fillable = [
        'brd_id',
        'name',
        'role',
        'department',
        'responsibility',
    ];

    public function brd(): BelongsTo
    {
        return $this->belongsTo(Brd::class);
    }
}

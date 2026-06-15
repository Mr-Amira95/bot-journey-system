<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MessageAttachment extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'message_id',
        'file_name',
        'file_path',
        'file_type',
        'mime_type',
        'size',
        'duration',
    ];

    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }
}

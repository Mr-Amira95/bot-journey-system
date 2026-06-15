<?php

namespace App\Enums;

enum CallType: string
{
    case Audio = 'audio';
    case Video = 'video';

    public function label(): string
    {
        return match($this) {
            self::Audio => 'Audio',
            self::Video => 'Video',
        };
    }
}

<?php

namespace App\Enums;

enum CallStatus: string
{
    case Ringing  = 'ringing';
    case Ongoing  = 'ongoing';
    case Ended    = 'ended';
    case Missed   = 'missed';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match($this) {
            self::Ringing  => 'Ringing',
            self::Ongoing  => 'Ongoing',
            self::Ended    => 'Ended',
            self::Missed   => 'Missed',
            self::Rejected => 'Rejected',
        };
    }
}

<?php

namespace App\Enums;

enum CallParticipantStatus: string
{
    case Invited  = 'invited';
    case Joined   = 'joined';
    case Left     = 'left';
    case Missed   = 'missed';
    case Rejected = 'rejected';
}

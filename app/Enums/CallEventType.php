<?php

namespace App\Enums;

enum CallEventType: string
{
    case Joined              = 'joined';
    case Left                = 'left';
    case Muted               = 'muted';
    case Unmuted             = 'unmuted';
    case VideoOn             = 'video_on';
    case VideoOff            = 'video_off';
    case ScreenShareStarted  = 'screen_share_started';
    case ScreenShareStopped  = 'screen_share_stopped';
    case Missed              = 'missed';
}

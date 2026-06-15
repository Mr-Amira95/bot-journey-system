<?php

namespace App\Enums;

enum BreakType: string
{
    case Lunch      = 'lunch';
    case ShortBreak = 'short_break';
    case Other      = 'other';
}

<?php

namespace App\Enums;

enum AttendanceType: string
{
    case CheckIn = 'check_in';
    case CheckOut = 'check_out';
    case BreakStart = 'break_start';
    case BreakEnd = 'break_end';
}

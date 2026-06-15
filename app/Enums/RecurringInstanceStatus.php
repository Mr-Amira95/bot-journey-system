<?php

namespace App\Enums;

enum RecurringInstanceStatus: string
{
    case Pending   = 'pending';
    case Generated = 'generated';
    case Skipped   = 'skipped';
}

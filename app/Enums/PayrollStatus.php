<?php

namespace App\Enums;

enum PayrollStatus: string
{
    case Draft    = 'draft';
    case Approved = 'approved';
    case Paid     = 'paid';
}

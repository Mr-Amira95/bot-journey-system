<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Bank     = 'bank';
    case Cash     = 'cash';
    case Card     = 'card';
    case Transfer = 'transfer';
}

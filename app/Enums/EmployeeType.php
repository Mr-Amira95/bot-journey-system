<?php

namespace App\Enums;

enum EmployeeType: string
{
    case HourlyEmployee = 'hourly_employee';
    case ContractEmployee = 'contract_employee';
}

<?php

namespace App\Enums;

enum ExpenseCategoryType: string
{
    case Operational     = 'operational';
    case Project         = 'project';
    case Salary          = 'salary';
    case Marketing       = 'marketing';
    case Tools           = 'tools';
    case Travel          = 'travel';
    case HourlyEmployees = 'hourly_employees';
    case Other           = 'other';
}

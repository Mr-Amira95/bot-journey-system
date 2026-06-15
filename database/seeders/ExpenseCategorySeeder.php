<?php

namespace Database\Seeders;

use App\Enums\ExpenseCategoryType;
use App\Models\ExpenseCategory;
use Illuminate\Database\Seeder;

class ExpenseCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['type' => ExpenseCategoryType::Operational->value,    'title' => 'Operational Costs'],
            ['type' => ExpenseCategoryType::Project->value,        'title' => 'Project Resources'],
            ['type' => ExpenseCategoryType::Salary->value,         'title' => 'Salaries & Compensation'],
            ['type' => ExpenseCategoryType::Marketing->value,      'title' => 'Marketing & Advertising'],
            ['type' => ExpenseCategoryType::Tools->value,          'title' => 'Software & Tools'],
            ['type' => ExpenseCategoryType::Travel->value,         'title' => 'Business Travel'],
            ['type' => ExpenseCategoryType::HourlyEmployees->value,'title' => 'Hourly Staff Payments'],
            ['type' => ExpenseCategoryType::Other->value,          'title' => 'Miscellaneous'],
        ];

        foreach ($categories as $cat) {
            ExpenseCategory::firstOrCreate(['title' => $cat['title']], $cat);
        }

        $this->command->info('Expense categories seeded: ' . count($categories));
    }
}

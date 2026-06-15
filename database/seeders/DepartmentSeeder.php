<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            ['name' => 'Engineering',      'description' => 'Software development, architecture, and infrastructure'],
            ['name' => 'Product',          'description' => 'Product management, roadmap planning, and strategy'],
            ['name' => 'Design',           'description' => 'UI/UX design, branding, and visual communication'],
            ['name' => 'Sales',            'description' => 'Business development, client acquisition, and revenue growth'],
            ['name' => 'Marketing',        'description' => 'Marketing campaigns, content, and growth initiatives'],
            ['name' => 'Human Resources',  'description' => 'People operations, talent acquisition, and culture'],
            ['name' => 'Finance',          'description' => 'Financial planning, accounting, and budgeting'],
            ['name' => 'Operations',       'description' => 'Business operations, process optimization, and logistics'],
            ['name' => 'Customer Success', 'description' => 'Client onboarding, retention, and support'],
            ['name' => 'AI Research',      'description' => 'Artificial intelligence and machine learning research'],
        ];

        foreach ($departments as $dept) {
            Department::firstOrCreate(['name' => $dept['name']], $dept);
        }

        $this->command->info('Departments seeded: ' . count($departments));
    }
}

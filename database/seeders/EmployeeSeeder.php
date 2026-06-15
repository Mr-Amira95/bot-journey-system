<?php

namespace Database\Seeders;

use App\Enums\EmployeeType;
use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $depts = Department::pluck('id', 'name');

        $employeesData = [
            [
                'email'         => 'sarah.chen@botjourney.ai',
                'dept'          => 'Engineering',
                'position'      => 'Chief Technology Officer',
                'type'          => EmployeeType::ContractEmployee->value,
                'salary'        => 180000.00,
                'hourly_rate'   => null,
                'hire_date'     => '2022-01-15',
                'manager_email' => null,
            ],
            [
                'email'         => 'james.wilson@botjourney.ai',
                'dept'          => 'Product',
                'position'      => 'Product Manager',
                'type'          => EmployeeType::ContractEmployee->value,
                'salary'        => 140000.00,
                'hourly_rate'   => null,
                'hire_date'     => '2022-03-01',
                'manager_email' => 'sarah.chen@botjourney.ai',
            ],
            [
                'email'         => 'emma.rodriguez@botjourney.ai',
                'dept'          => 'Engineering',
                'position'      => 'Senior Software Engineer',
                'type'          => EmployeeType::ContractEmployee->value,
                'salary'        => 125000.00,
                'hourly_rate'   => null,
                'hire_date'     => '2022-06-15',
                'manager_email' => 'sarah.chen@botjourney.ai',
            ],
            [
                'email'         => 'liam.thompson@botjourney.ai',
                'dept'          => 'Engineering',
                'position'      => 'Frontend Developer',
                'type'          => EmployeeType::HourlyEmployee->value,
                'salary'        => null,
                'hourly_rate'   => 65.00,
                'hire_date'     => '2023-02-10',
                'manager_email' => 'emma.rodriguez@botjourney.ai',
            ],
            [
                'email'         => 'olivia.davis@botjourney.ai',
                'dept'          => 'Design',
                'position'      => 'UX Designer',
                'type'          => EmployeeType::ContractEmployee->value,
                'salary'        => 95000.00,
                'hourly_rate'   => null,
                'hire_date'     => '2022-09-01',
                'manager_email' => 'james.wilson@botjourney.ai',
            ],
            [
                'email'         => 'noah.martinez@botjourney.ai',
                'dept'          => 'Engineering',
                'position'      => 'Backend Engineer',
                'type'          => EmployeeType::ContractEmployee->value,
                'salary'        => 115000.00,
                'hourly_rate'   => null,
                'hire_date'     => '2023-01-05',
                'manager_email' => 'emma.rodriguez@botjourney.ai',
            ],
            [
                'email'         => 'ava.johnson@botjourney.ai',
                'dept'          => 'AI Research',
                'position'      => 'Data Scientist',
                'type'          => EmployeeType::ContractEmployee->value,
                'salary'        => 130000.00,
                'hourly_rate'   => null,
                'hire_date'     => '2022-11-20',
                'manager_email' => 'sarah.chen@botjourney.ai',
            ],
            [
                'email'         => 'william.brown@botjourney.ai',
                'dept'          => 'Engineering',
                'position'      => 'DevOps Engineer',
                'type'          => EmployeeType::HourlyEmployee->value,
                'salary'        => null,
                'hourly_rate'   => 75.00,
                'hire_date'     => '2023-04-01',
                'manager_email' => 'emma.rodriguez@botjourney.ai',
            ],
            [
                'email'         => 'isabella.garcia@botjourney.ai',
                'dept'          => 'Marketing',
                'position'      => 'Marketing Manager',
                'type'          => EmployeeType::ContractEmployee->value,
                'salary'        => 105000.00,
                'hourly_rate'   => null,
                'hire_date'     => '2022-08-15',
                'manager_email' => null,
            ],
            [
                'email'         => 'lucas.anderson@botjourney.ai',
                'dept'          => 'Sales',
                'position'      => 'Sales Manager',
                'type'          => EmployeeType::ContractEmployee->value,
                'salary'        => 110000.00,
                'hourly_rate'   => null,
                'hire_date'     => '2022-07-01',
                'manager_email' => null,
            ],
            [
                'email'         => 'mia.taylor@botjourney.ai',
                'dept'          => 'Customer Success',
                'position'      => 'Customer Success Manager',
                'type'          => EmployeeType::ContractEmployee->value,
                'salary'        => 85000.00,
                'hourly_rate'   => null,
                'hire_date'     => '2023-03-15',
                'manager_email' => 'lucas.anderson@botjourney.ai',
            ],
            [
                'email'         => 'ethan.lee@botjourney.ai',
                'dept'          => 'Finance',
                'position'      => 'Finance Manager',
                'type'          => EmployeeType::ContractEmployee->value,
                'salary'        => 120000.00,
                'hourly_rate'   => null,
                'hire_date'     => '2022-05-01',
                'manager_email' => null,
            ],
            [
                'email'         => 'charlotte.clark@botjourney.ai',
                'dept'          => 'Human Resources',
                'position'      => 'HR Manager',
                'type'          => EmployeeType::ContractEmployee->value,
                'salary'        => 100000.00,
                'hourly_rate'   => null,
                'hire_date'     => '2022-04-01',
                'manager_email' => null,
            ],
            [
                'email'         => 'alexander.wright@botjourney.ai',
                'dept'          => 'AI Research',
                'position'      => 'AI Researcher',
                'type'          => EmployeeType::HourlyEmployee->value,
                'salary'        => null,
                'hourly_rate'   => 90.00,
                'hire_date'     => '2023-06-01',
                'manager_email' => 'ava.johnson@botjourney.ai',
            ],
            [
                'email'         => 'amelia.lewis@botjourney.ai',
                'dept'          => 'Design',
                'position'      => 'Product Designer',
                'type'          => EmployeeType::HourlyEmployee->value,
                'salary'        => null,
                'hourly_rate'   => 55.00,
                'hire_date'     => '2023-07-15',
                'manager_email' => 'olivia.davis@botjourney.ai',
            ],
        ];

        // First pass: create all employees
        $employeeByEmail = [];
        foreach ($employeesData as $data) {
            $user = User::where('email', $data['email'])->first();
            if (!$user) continue;

            $emp = Employee::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'department_id' => $depts[$data['dept']] ?? null,
                    'position'      => $data['position'],
                    'hire_date'     => $data['hire_date'],
                    'type'          => $data['type'],
                    'salary'        => $data['salary'],
                    'hourly_rate'   => $data['hourly_rate'],
                ]
            );
            $employeeByEmail[$data['email']] = $emp;
        }

        // Second pass: assign managers
        foreach ($employeesData as $data) {
            if (!$data['manager_email']) continue;
            $emp = $employeeByEmail[$data['email']] ?? null;
            $mgr = $employeeByEmail[$data['manager_email']] ?? null;
            if ($emp && $mgr && !$emp->manager_id) {
                $emp->update(['manager_id' => $mgr->id]);
            }
        }

        $this->command->info('Employees seeded: ' . count($employeeByEmail));
    }
}

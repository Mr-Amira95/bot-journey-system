<?php

namespace Database\Seeders;

use App\Enums\UserStatus;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $modules = [
            'users'       => ['View Users',       'Create Users',       'Edit Users',       'Delete Users'],
            'employees'   => ['View Employees',   'Create Employees',   'Edit Employees',   'Delete Employees'],
            'clients'     => ['View Clients',     'Create Clients',     'Edit Clients',     'Delete Clients'],
            'projects'           => ['View Projects',           'Create Projects',           'Edit Projects',           'Delete Projects'],
            'expenses'           => ['View Expenses',           'Create Expenses',           'Edit Expenses',           'Delete Expenses'],
            'expense_categories' => ['View Expense Categories', 'Create Expense Categories', 'Edit Expense Categories', 'Delete Expense Categories'],
            'tasks'              => ['View Tasks',              'Create Tasks',              'Edit Tasks',              'Delete Tasks'],
            'whiteboards'         => ['View Whiteboards',         'Create Whiteboards',         'Edit Whiteboards',         'Delete Whiteboards'],
            'conversations'       => ['View Conversations',       'Create Conversations',       'Edit Conversations',       'Delete Conversations'],
            'calls'               => ['View Calls',               'Create Calls',               'Edit Calls',               'Delete Calls'],
            'departments'        => ['View Departments',        'Create Departments',        'Edit Departments',        'Delete Departments'],
            'roles'              => ['View Roles',              'Create Roles',              'Edit Roles',              'Delete Roles'],
            'attendance'         => ['View Attendance',         'Create Attendance',         'Edit Attendance',         'Delete Attendance'],
            'employee_breaks'    => ['View Employee Breaks',    'Create Employee Breaks',    'Edit Employee Breaks',    'Delete Employee Breaks'],
            'leave_requests'     => ['View Leave Requests',     'Create Leave Requests',     'Edit Leave Requests',     'Delete Leave Requests'],
            'leave_types'        => ['View Leave Types',        'Create Leave Types',        'Edit Leave Types',        'Delete Leave Types'],
            'leave_balances'     => ['View Leave Balances',     'Create Leave Balances',     'Edit Leave Balances',     'Delete Leave Balances'],
            'overtime_requests'  => ['View Overtime Requests',  'Create Overtime Requests',  'Edit Overtime Requests',  'Delete Overtime Requests'],
            'payroll'            => ['View Payroll',            'Create Payroll',            'Edit Payroll',            'Delete Payroll'],
            'salary_histories'   => ['View Salary Histories',   'Create Salary Histories',   'Edit Salary Histories',   'Delete Salary Histories'],
            'invoices'           => ['View Invoices',           'Create Invoices',           'Edit Invoices',           'Delete Invoices'],
            'recurring_expenses' => ['View Recurring Expenses', 'Create Recurring Expenses', 'Edit Recurring Expenses', 'Delete Recurring Expenses'],
            'project_budgets'    => ['View Project Budgets',    'Create Project Budgets',    'Edit Project Budgets',    'Delete Project Budgets'],
            'work_schedules'     => ['View Work Schedules',     'Create Work Schedules',     'Edit Work Schedules',     'Delete Work Schedules'],
            'email_templates'    => ['View Email Templates',    'Create Email Templates',    'Edit Email Templates',    'Delete Email Templates'],
            'activity_logs'      => ['View Activity Logs',      'Create Activity Logs',      'Edit Activity Logs',      'Delete Activity Logs'],
        ];

        $actions = ['view', 'create', 'edit', 'delete'];

        $permissionIds = [];

        foreach ($modules as $module => $names) {
            foreach (array_combine($actions, $names) as $action => $name) {
                $permission = Permission::firstOrCreate(
                    ['slug' => "{$action}_{$module}"],
                    ['name' => $name, 'module' => $module]
                );
                $permissionIds[] = $permission->id;
            }
        }

        $role = Role::firstOrCreate(
            ['slug' => 'super-admin'],
            ['name' => 'Super Admin']
        );

        // Oversight permissions — not standard CRUD, created individually
        $oversight = [
            ['slug' => 'view_all_messages',    'name' => 'View All Users Messages',    'module' => 'oversight'],
            ['slug' => 'view_all_whiteboards', 'name' => 'View All Users Whiteboards', 'module' => 'oversight'],
            ['slug' => 'view_all_tasks',       'name' => 'View All Users Tasks',       'module' => 'oversight'],
            ['slug' => 'view_all_expenses',    'name' => 'View All Users Expenses',    'module' => 'oversight'],
            ['slug' => 'send_employee_documents',    'name' => 'Send Employee Documents',    'module' => 'employees'],
            ['slug' => 'view_employee_attachments',  'name' => 'View Employee Attachments',  'module' => 'employees'],
            ['slug' => 'manage_employee_attachments','name' => 'Manage Employee Attachments','module' => 'employees'],
            ['slug' => 'view_all_attendance',        'name' => 'View All Users Attendance',        'module' => 'oversight'],
            ['slug' => 'view_all_leave_requests',    'name' => 'View All Leave Requests',          'module' => 'oversight'],
            ['slug' => 'approve_leave_requests',     'name' => 'Approve Leave Requests',           'module' => 'oversight'],
            ['slug' => 'view_all_overtime_requests', 'name' => 'View All Overtime Requests',       'module' => 'oversight'],
            ['slug' => 'approve_overtime_requests',  'name' => 'Approve Overtime Requests',        'module' => 'oversight'],
            ['slug' => 'view_all_employee_breaks',   'name' => 'View All Employee Breaks',         'module' => 'oversight'],
            ['slug' => 'approve_payroll',            'name' => 'Approve Payroll',                  'module' => 'oversight'],
            ['slug' => 'mark_payroll_paid',          'name' => 'Mark Payroll as Paid',             'module' => 'oversight'],
            ['slug' => 'record_invoice_payment',     'name' => 'Record Invoice Payment',           'module' => 'oversight'],
        ];

        foreach ($oversight as $item) {
            $permission = Permission::firstOrCreate(
                ['slug' => $item['slug']],
                ['name' => $item['name'], 'module' => $item['module']]
            );
            $permissionIds[] = $permission->id;
        }

        $role->permissions()->sync($permissionIds);

        $user = User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name'     => 'Super Admin',
                'password' => Hash::make('password'),
                'status'   => UserStatus::Active,
            ]
        );

        $user->roles()->syncWithoutDetaching([$role->id]);

        $this->command->info('SuperAdmin seeded: admin@admin.com / password');
        $this->command->info('Permissions created: ' . count($permissionIds));
    }
}

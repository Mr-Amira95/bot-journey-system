<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class HRPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // Leave Types
            ['name' => 'View Leave Types',   'slug' => 'view_leave_types',   'module' => 'leave_types'],
            ['name' => 'Create Leave Types',  'slug' => 'create_leave_types',  'module' => 'leave_types'],
            ['name' => 'Edit Leave Types',    'slug' => 'edit_leave_types',    'module' => 'leave_types'],
            ['name' => 'Delete Leave Types',  'slug' => 'delete_leave_types',  'module' => 'leave_types'],
            // Leave Requests
            ['name' => 'View Leave Requests',    'slug' => 'view_leave_requests',     'module' => 'leave_requests'],
            ['name' => 'View All Leave Requests','slug' => 'view_all_leave_requests',  'module' => 'leave_requests'],
            ['name' => 'Create Leave Requests',  'slug' => 'create_leave_requests',   'module' => 'leave_requests'],
            ['name' => 'Edit Leave Requests',    'slug' => 'edit_leave_requests',     'module' => 'leave_requests'],
            ['name' => 'Approve Leave Requests', 'slug' => 'approve_leave_requests',  'module' => 'leave_requests'],
            ['name' => 'Delete Leave Requests',  'slug' => 'delete_leave_requests',   'module' => 'leave_requests'],
            // Leave Balances
            ['name' => 'View Leave Balances',   'slug' => 'view_leave_balances',   'module' => 'leave_balances'],
            ['name' => 'Create Leave Balances', 'slug' => 'create_leave_balances', 'module' => 'leave_balances'],
            ['name' => 'Edit Leave Balances',   'slug' => 'edit_leave_balances',   'module' => 'leave_balances'],
            ['name' => 'Delete Leave Balances', 'slug' => 'delete_leave_balances', 'module' => 'leave_balances'],
            // Overtime Requests
            ['name' => 'View Overtime Requests',     'slug' => 'view_overtime_requests',     'module' => 'overtime_requests'],
            ['name' => 'View All Overtime Requests', 'slug' => 'view_all_overtime_requests',  'module' => 'overtime_requests'],
            ['name' => 'Create Overtime Requests',   'slug' => 'create_overtime_requests',   'module' => 'overtime_requests'],
            ['name' => 'Edit Overtime Requests',     'slug' => 'edit_overtime_requests',     'module' => 'overtime_requests'],
            ['name' => 'Approve Overtime Requests',  'slug' => 'approve_overtime_requests',  'module' => 'overtime_requests'],
            ['name' => 'Delete Overtime Requests',   'slug' => 'delete_overtime_requests',   'module' => 'overtime_requests'],
            // Employee Breaks
            ['name' => 'View Employee Breaks',     'slug' => 'view_employee_breaks',     'module' => 'employee_breaks'],
            ['name' => 'View All Employee Breaks', 'slug' => 'view_all_employee_breaks', 'module' => 'employee_breaks'],
            ['name' => 'Create Employee Breaks',   'slug' => 'create_employee_breaks',   'module' => 'employee_breaks'],
            ['name' => 'Edit Employee Breaks',     'slug' => 'edit_employee_breaks',     'module' => 'employee_breaks'],
            ['name' => 'Delete Employee Breaks',   'slug' => 'delete_employee_breaks',   'module' => 'employee_breaks'],
            // Work Schedules
            ['name' => 'View Work Schedules',   'slug' => 'view_work_schedules',   'module' => 'work_schedules'],
            ['name' => 'Create Work Schedules', 'slug' => 'create_work_schedules', 'module' => 'work_schedules'],
            ['name' => 'Edit Work Schedules',   'slug' => 'edit_work_schedules',   'module' => 'work_schedules'],
            ['name' => 'Delete Work Schedules', 'slug' => 'delete_work_schedules', 'module' => 'work_schedules'],
        ];

        foreach ($permissions as $p) {
            Permission::firstOrCreate(['slug' => $p['slug']], $p);
        }

        $hrModules = [
            'leave_types', 'leave_requests', 'leave_balances',
            'overtime_requests', 'employee_breaks', 'work_schedules',
        ];

        // Manager gets all HR permissions
        $manager = Role::where('slug', 'manager')->first();
        if ($manager) {
            $ids = Permission::whereIn('module', $hrModules)->pluck('id');
            $manager->permissions()->syncWithoutDetaching($ids);
        }

        // Employee gets self-service permissions only
        $employee = Role::where('slug', 'employee')->first();
        if ($employee) {
            $employeeSlugs = [
                'view_leave_requests',
                'create_leave_requests',
                'view_overtime_requests',
                'create_overtime_requests',
                'view_employee_breaks',
                'create_employee_breaks',
            ];
            $ids = Permission::whereIn('slug', $employeeSlugs)->pluck('id');
            $employee->permissions()->syncWithoutDetaching($ids);
        }

        $this->command->info('HR permissions seeded and assigned to Manager and Employee roles.');
    }
}

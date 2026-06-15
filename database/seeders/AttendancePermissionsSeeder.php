<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class AttendancePermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['name' => 'View Attendance',     'slug' => 'view_attendance',     'module' => 'attendance'],
            ['name' => 'View All Attendance', 'slug' => 'view_all_attendance', 'module' => 'attendance'],
            ['name' => 'Create Attendance',   'slug' => 'create_attendance',   'module' => 'attendance'],
            ['name' => 'Edit Attendance',     'slug' => 'edit_attendance',     'module' => 'attendance'],
            ['name' => 'Delete Attendance',   'slug' => 'delete_attendance',   'module' => 'attendance'],
        ];

        foreach ($permissions as $p) {
            Permission::firstOrCreate(['slug' => $p['slug']], $p);
        }

        $allIds = Permission::where('module', 'attendance')->pluck('id');

        // Super-admin gets every permission
        $superAdmin = Role::where('slug', 'super-admin')->first();
        if ($superAdmin) {
            $superAdmin->permissions()->syncWithoutDetaching($allIds);
        }

        // Manager gets all attendance permissions
        $manager = Role::where('slug', 'manager')->first();
        if ($manager) {
            $manager->permissions()->syncWithoutDetaching($allIds);
        }

        // Employee can only view their own attendance (clock-in/out is ungated by design)
        $employee = Role::where('slug', 'employee')->first();
        if ($employee) {
            $ids = Permission::where('slug', 'view_attendance')->pluck('id');
            $employee->permissions()->syncWithoutDetaching($ids);
        }

        $this->command->info('Attendance permissions seeded and assigned to Super Admin, Manager, and Employee roles.');
    }
}

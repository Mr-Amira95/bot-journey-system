<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class BrdPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['name' => 'View BRDs',       'slug' => 'view_brds',       'module' => 'brds'],
            ['name' => 'View All BRDs',   'slug' => 'view_all_brds',   'module' => 'brds'],
            ['name' => 'Create BRDs',     'slug' => 'create_brds',     'module' => 'brds'],
            ['name' => 'Edit BRDs',       'slug' => 'edit_brds',       'module' => 'brds'],
            ['name' => 'Approve BRDs',    'slug' => 'approve_brds',    'module' => 'brds'],
            ['name' => 'Delete BRDs',     'slug' => 'delete_brds',     'module' => 'brds'],
            ['name' => 'Export BRDs',     'slug' => 'export_brds',     'module' => 'brds'],
        ];

        foreach ($permissions as $p) {
            Permission::firstOrCreate(['slug' => $p['slug']], $p);
        }

        // Manager gets full oversight of BRDs (create, approve, edit, delete, export, view all)
        $manager = Role::where('slug', 'manager')->first();
        if ($manager) {
            $ids = Permission::where('module', 'brds')->pluck('id');
            $manager->permissions()->syncWithoutDetaching($ids);
        }

        // Employee can create and view/export their own BRDs, but not approve them
        $employee = Role::where('slug', 'employee')->first();
        if ($employee) {
            $employeeSlugs = ['view_brds', 'create_brds', 'edit_brds', 'export_brds'];
            $ids = Permission::whereIn('slug', $employeeSlugs)->pluck('id');
            $employee->permissions()->syncWithoutDetaching($ids);
        }

        $this->command->info('BRD permissions seeded and assigned to Manager and Employee roles.');
    }
}

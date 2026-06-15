<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Manager: full CRUD on operational modules
        $manager = Role::firstOrCreate(
            ['slug' => 'manager'],
            ['name' => 'Manager']
        );

        $managerPermissions = Permission::where(function ($q) {
            $q->whereIn('module', ['projects', 'tasks', 'expenses', 'expense_categories', 'clients', 'conversations', 'calls', 'whiteboards', 'employees'])
              ->whereIn('slug', function ($sub) {
                  $sub->select('slug')->from('permissions')
                      ->where('slug', 'like', 'view_%')
                      ->orWhere('slug', 'like', 'create_%')
                      ->orWhere('slug', 'like', 'edit_%');
              });
        })->pluck('id');

        $manager->permissions()->sync($managerPermissions);

        // Employee: view-only on work modules
        $employee = Role::firstOrCreate(
            ['slug' => 'employee'],
            ['name' => 'Employee']
        );

        $employeePermissions = Permission::where('slug', 'like', 'view_%')
            ->whereIn('module', ['projects', 'tasks', 'conversations', 'calls', 'whiteboards'])
            ->pluck('id');

        $employee->permissions()->sync($employeePermissions);

        // Client: view projects and tasks only
        $client = Role::firstOrCreate(
            ['slug' => 'client'],
            ['name' => 'Client']
        );

        $clientPermissions = Permission::whereIn('slug', ['view_projects', 'view_tasks'])
            ->pluck('id');

        $client->permissions()->sync($clientPermissions);

        $this->command->info('Roles seeded: manager, employee, client');
    }
}

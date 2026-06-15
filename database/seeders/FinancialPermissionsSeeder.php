<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class FinancialPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // Salary Histories
            ['name' => 'View Salary Histories',   'slug' => 'view_salary_histories',   'module' => 'salary_histories'],
            ['name' => 'Create Salary Histories',  'slug' => 'create_salary_histories',  'module' => 'salary_histories'],
            ['name' => 'Edit Salary Histories',    'slug' => 'edit_salary_histories',    'module' => 'salary_histories'],
            ['name' => 'Delete Salary Histories',  'slug' => 'delete_salary_histories',  'module' => 'salary_histories'],
            // Payroll
            ['name' => 'View Payroll',    'slug' => 'view_payroll',    'module' => 'payroll'],
            ['name' => 'Create Payroll',  'slug' => 'create_payroll',  'module' => 'payroll'],
            ['name' => 'Edit Payroll',    'slug' => 'edit_payroll',    'module' => 'payroll'],
            ['name' => 'Approve Payroll', 'slug' => 'approve_payroll', 'module' => 'payroll'],
            ['name' => 'Delete Payroll',  'slug' => 'delete_payroll',  'module' => 'payroll'],
            // Invoices
            ['name' => 'View Invoices',   'slug' => 'view_invoices',   'module' => 'invoices'],
            ['name' => 'Create Invoices', 'slug' => 'create_invoices', 'module' => 'invoices'],
            ['name' => 'Edit Invoices',   'slug' => 'edit_invoices',   'module' => 'invoices'],
            ['name' => 'Delete Invoices', 'slug' => 'delete_invoices', 'module' => 'invoices'],
            // Payments
            ['name' => 'View Payments',   'slug' => 'view_payments',   'module' => 'payments'],
            ['name' => 'Create Payments', 'slug' => 'create_payments', 'module' => 'payments'],
            ['name' => 'Edit Payments',   'slug' => 'edit_payments',   'module' => 'payments'],
            ['name' => 'Delete Payments', 'slug' => 'delete_payments', 'module' => 'payments'],
            // Recurring Expenses
            ['name' => 'View Recurring Expenses',   'slug' => 'view_recurring_expenses',   'module' => 'recurring_expenses'],
            ['name' => 'Create Recurring Expenses', 'slug' => 'create_recurring_expenses', 'module' => 'recurring_expenses'],
            ['name' => 'Edit Recurring Expenses',   'slug' => 'edit_recurring_expenses',   'module' => 'recurring_expenses'],
            ['name' => 'Delete Recurring Expenses', 'slug' => 'delete_recurring_expenses', 'module' => 'recurring_expenses'],
            // Project Budgets
            ['name' => 'View Project Budgets',   'slug' => 'view_project_budgets',   'module' => 'project_budgets'],
            ['name' => 'Create Project Budgets', 'slug' => 'create_project_budgets', 'module' => 'project_budgets'],
            ['name' => 'Edit Project Budgets',   'slug' => 'edit_project_budgets',   'module' => 'project_budgets'],
            ['name' => 'Delete Project Budgets', 'slug' => 'delete_project_budgets', 'module' => 'project_budgets'],
        ];

        foreach ($permissions as $p) {
            Permission::firstOrCreate(['slug' => $p['slug']], $p);
        }

        // Grant all financial permissions to Manager role
        $manager = Role::where('slug', 'manager')->first();
        if ($manager) {
            $ids = Permission::whereIn('module', [
                'salary_histories', 'payroll', 'invoices',
                'payments', 'recurring_expenses', 'project_budgets',
            ])->pluck('id');
            $manager->permissions()->syncWithoutDetaching($ids);
        }

        $this->command->info('Financial permissions seeded and assigned to Manager.');
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // 1. Permissions, roles, and super-admin user
            SuperAdminSeeder::class,

            // 2. Additional roles (manager, employee, client)
            RoleSeeder::class,

            // 3. Departments
            DepartmentSeeder::class,

            // 4. Team members and client users
            UserSeeder::class,

            // 5. Employee records linked to users and departments
            EmployeeSeeder::class,

            // 6. Attendance records for team members
            AttendanceSeeder::class,

            // 7. Client companies linked to client users
            ClientSeeder::class,

            // 8. Projects with members and status logs
            ProjectSeeder::class,

            // 9. Expense categories
            ExpenseCategorySeeder::class,

            // 10. Expenses across projects
            ExpenseSeeder::class,

            // 11. Tasks with assignees, logs, comments, and dependencies
            TaskSeeder::class,

            // 12. Conversations (direct + group) with messages
            ConversationSeeder::class,

            // 13. Calls with participants, screen shares, and events
            CallSeeder::class,

            // 14. Whiteboards with shares
            WhiteboardSeeder::class,

            // 15. Activity logs
            ActivityLogSeeder::class,

            // 16. Financial module permissions
            FinancialPermissionsSeeder::class,

            // 17. HR module permissions
            HRPermissionsSeeder::class,

            // 18. Attendance module permissions
            AttendancePermissionsSeeder::class,
        ]);
    }
}

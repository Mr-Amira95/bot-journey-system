<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $sqlite = DB::connection()->getDriverName() === 'sqlite';

        DB::statement('DROP VIEW IF EXISTS v_daily_attendance');
        DB::statement('DROP VIEW IF EXISTS v_employee_leave_summary');
        DB::statement('DROP VIEW IF EXISTS v_overtime_summary');
        DB::statement('DROP VIEW IF EXISTS v_pending_approvals');
        DB::statement($this->dailyAttendanceView());
        DB::statement($this->employeeLeaveSummaryView());
        DB::statement($this->overtimeSummaryView($sqlite));
        DB::statement($this->pendingApprovalsView());
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS v_pending_approvals');
        DB::statement('DROP VIEW IF EXISTS v_overtime_summary');
        DB::statement('DROP VIEW IF EXISTS v_employee_leave_summary');
        DB::statement('DROP VIEW IF EXISTS v_daily_attendance');
    }

    private function dailyAttendanceView(): string
    {
        return 'CREATE VIEW v_daily_attendance AS
            SELECT
                ea.user_id,
                DATE(ea.time_date)                                                   AS work_date,
                MIN(CASE WHEN ea.type = \'check_in\'  THEN ea.time_date END)         AS check_in_time,
                MAX(CASE WHEN ea.type = \'check_out\' THEN ea.time_date END)         AS check_out_time,
                COUNT(CASE WHEN ea.type = \'check_in\' THEN 1 END)                  AS sessions
            FROM employee_attendance ea
            GROUP BY ea.user_id, DATE(ea.time_date)';
    }

    private function employeeLeaveSummaryView(): string
    {
        return 'CREATE VIEW v_employee_leave_summary AS
            SELECT
                lb.employee_id,
                lb.year,
                lt.id                                  AS leave_type_id,
                lt.name                                AS leave_type,
                lt.is_paid,
                lb.allocated_days,
                lb.used_days,
                (lb.allocated_days - lb.used_days)     AS remaining_days
            FROM leave_balances lb
            JOIN leave_types lt ON lt.id = lb.leave_type_id';
    }

    private function overtimeSummaryView(bool $sqlite): string
    {
        $monthFn = $sqlite
            ? "strftime('%Y-%m', ot.date)"
            : "DATE_FORMAT(ot.date, '%Y-%m')";

        return "CREATE VIEW v_overtime_summary AS
            SELECT
                ot.employee_id,
                {$monthFn}                                                          AS month,
                COUNT(*)                                                            AS request_count,
                SUM(ot.hours)                                                       AS total_hours,
                SUM(CASE WHEN ot.status = 'approved' THEN ot.hours ELSE 0 END)     AS approved_hours,
                SUM(CASE WHEN ot.status = 'pending'  THEN ot.hours ELSE 0 END)     AS pending_hours
            FROM overtime_requests ot
            GROUP BY ot.employee_id, {$monthFn}";
    }

    private function pendingApprovalsView(): string
    {
        return "CREATE VIEW v_pending_approvals AS
            SELECT
                'leave'    AS approval_type,
                lr.id      AS request_id,
                lr.employee_id,
                lt.name    AS detail,
                lr.status,
                lr.created_at
            FROM leave_requests lr
            JOIN leave_types lt ON lt.id = lr.leave_type_id
            WHERE lr.status = 'pending'
              AND lr.deleted_at IS NULL

            UNION ALL

            SELECT
                'overtime' AS approval_type,
                ot.id      AS request_id,
                ot.employee_id,
                ot.date    AS detail,
                ot.status,
                ot.created_at
            FROM overtime_requests ot
            WHERE ot.status = 'pending'";
    }
};

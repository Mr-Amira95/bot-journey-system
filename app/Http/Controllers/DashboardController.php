<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Employee;
use App\Models\Client;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\LeaveRequest;
use App\Models\OvertimeRequest;
use App\Models\Project;
use App\Models\Task;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        return redirect()->route('whiteboards.index');

        $today = Carbon::today();

        // ── Basic counts ───────────────────────────────────────────────────────
        $employeeCount   = Employee::count();
        $clientCount     = Client::count();
        $departmentCount = Department::count();

        // ── Projects & Tasks ──────────────────────────────────────────────────
        $projectsByStatus = Project::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $openProjectsCount = Project::whereIn('status', ['planning', 'active'])->count();

        $tasksByStatus = Task::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $overdueTasksCount = Task::where('due_date', '<', $today)
            ->whereNotIn('status', ['done'])
            ->count();

        $overdueTasks = Task::with(['project', 'assignees'])
            ->where('due_date', '<', $today)
            ->whereNotIn('status', ['done'])
            ->latest('due_date')
            ->limit(8)
            ->get();

        // ── HR ────────────────────────────────────────────────────────────────
        $pendingLeaveCount    = LeaveRequest::where('status', 'pending')->count();
        $pendingOvertimeCount = OvertimeRequest::where('status', 'pending')->count();

        $pendingLeaveRequests = LeaveRequest::with(['employee.user', 'leaveType'])
            ->where('status', 'pending')
            ->latest()
            ->limit(8)
            ->get();

        $leaveByMonth = $this->monthlyGrouped(
            LeaveRequest::class,
            'start_date',
            6,
            fn ($q) => $q->selectRaw('count(*) as total')->value('total')
        );

        // ── Finance ───────────────────────────────────────────────────────────
        $unpaidInvoicesCount = Invoice::whereIn('status', ['sent', 'overdue'])->count();

        $invoiceStats = Invoice::selectRaw('status, count(*) as total, sum(total) as amount')
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        $expensesByMonth = $this->monthlyGrouped(
            Expense::class,
            'expense_date',
            6,
            fn ($q) => $q->selectRaw('sum(amount) as total')->value('total')
        );

        $revenueByMonth = $this->monthlyGrouped(
            Invoice::class,
            'issue_date',
            6,
            fn ($q) => $q->where('status', 'paid')->selectRaw('sum(total) as total')->value('total')
        );

        $thisMonthExpenses = Expense::whereYear('expense_date', $today->year)
            ->whereMonth('expense_date', $today->month)
            ->sum('amount');

        return view('dashboard.index', compact(
            'employeeCount', 'clientCount', 'departmentCount',
            'projectsByStatus', 'openProjectsCount',
            'tasksByStatus', 'overdueTasksCount', 'overdueTasks',
            'pendingLeaveCount', 'pendingOvertimeCount',
            'pendingLeaveRequests', 'leaveByMonth',
            'unpaidInvoicesCount', 'invoiceStats',
            'expensesByMonth', 'revenueByMonth', 'thisMonthExpenses',
        ));
    }

    private function monthlyGrouped(string $model, string $dateColumn, int $months, \Closure $query): array
    {
        $labels = [];
        $values = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $date     = Carbon::today()->subMonths($i);
            $labels[] = $date->format('M Y');
            $values[] = (float) ($query($model::whereYear($dateColumn, $date->year)->whereMonth($dateColumn, $date->month)) ?? 0);
        }

        return ['labels' => $labels, 'values' => $values];
    }
}

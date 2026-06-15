<?php

namespace App\Http\Controllers;

use App\Exports\AttendanceExport;
use App\Exports\ExpensesExport;
use App\Models\Employee;
use App\Models\EmployeeAttendance;
use App\Models\Expense;
use App\Models\PayrollRun;
use App\Models\Project;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function payrollPdf(PayrollRun $payrollRun)
    {
        $payrollRun->load(['items.employee.user', 'createdBy', 'approvedBy']);

        $pdf = Pdf::loadView('reports.payroll', compact('payrollRun'))
            ->setPaper('a4', 'portrait');

        return $pdf->download("payroll-{$payrollRun->period_start->format('Y-m')}.pdf");
    }

    public function expensesPdf(Request $request)
    {
        $expenses = $this->expenseQuery($request)->get();
        $total    = $expenses->sum('amount');

        $pdf = Pdf::loadView('reports.expenses', compact('expenses', 'total'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('expenses-report.pdf');
    }

    public function expensesExcel(Request $request)
    {
        return Excel::download(new ExpensesExport($request), 'expenses-report.xlsx');
    }

    public function attendancePdf(Request $request, Employee $employee)
    {
        $month = $request->integer('month', now()->month);
        $year  = $request->integer('year', now()->year);

        $records = EmployeeAttendance::where('user_id', $employee->user_id)
            ->whereYear('time_date', $year)
            ->whereMonth('time_date', $month)
            ->orderBy('time_date')
            ->get();

        $employee->load('user', 'department');
        $monthLabel = Carbon::create($year, $month)->format('F Y');

        $pdf = Pdf::loadView('reports.attendance', compact('employee', 'records', 'monthLabel'))
            ->setPaper('a4', 'portrait');

        return $pdf->download("attendance-{$employee->id}-{$year}-{$month}.pdf");
    }

    public function projectPdf(Project $project)
    {
        $project->load([
            'client', 'creator', 'members.user',
            'tasks', 'attachments',
        ]);

        $taskStats = [
            'total'    => $project->tasks->count(),
            'done'     => $project->tasks->where('status', 'done')->count(),
            'overdue'  => $project->tasks->filter(fn ($t) => $t->due_date && $t->due_date->isPast() && $t->status->value !== 'done')->count(),
        ];

        $pdf = Pdf::loadView('reports.project', compact('project', 'taskStats'))
            ->setPaper('a4', 'portrait');

        return $pdf->download("project-{$project->id}.pdf");
    }

    private function expenseQuery(Request $request)
    {
        $query = Expense::with(['category', 'paidBy', 'project'])
            ->orderBy('expense_date', 'desc');

        if ($request->filled('from')) {
            $query->where('expense_date', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->where('expense_date', '<=', $request->to);
        }
        if ($request->filled('employee_id')) {
            $query->where('paid_by', $request->employee_id);
        }

        return $query;
    }
}

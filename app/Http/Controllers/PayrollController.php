<?php

namespace App\Http\Controllers;

use App\Enums\PayrollStatus;
use App\Models\Employee;
use App\Models\PayrollRun;
use Illuminate\Http\Request;

class PayrollController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('view_payroll'), 403);
        $query = PayrollRun::with(['createdBy', 'approvedBy', 'items.employee.user']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('period_start', 'like', '%' . $request->search . '%')
                  ->orWhere('period_end', 'like', '%' . $request->search . '%')
                  ->orWhere('notes', 'like', '%' . $request->search . '%');
            });
        }

        $runs      = $query->latest('period_start')->paginate(15)->withQueryString();
        $statuses  = PayrollStatus::cases();
        $employees = Employee::with('user')->get();

        return view('payroll.index', compact('runs', 'statuses', 'employees'));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('create_payroll'), 403);
        $request->validate([
            'period_start'            => ['required', 'date'],
            'period_end'              => ['required', 'date', 'after_or_equal:period_start'],
            'notes'                   => ['nullable', 'string'],
            'items'                   => ['required', 'array', 'min:1'],
            'items.*.employee_id'     => ['required', 'exists:employees,id'],
            'items.*.base_salary'     => ['required', 'numeric', 'min:0'],
            'items.*.bonuses'         => ['nullable', 'numeric', 'min:0'],
            'items.*.deductions'      => ['nullable', 'numeric', 'min:0'],
            'items.*.notes'           => ['nullable', 'string'],
        ]);

        $run = PayrollRun::create([
            'period_start' => $request->period_start,
            'period_end'   => $request->period_end,
            'notes'        => $request->notes,
            'status'       => PayrollStatus::Draft->value,
            'created_by'   => auth()->id(),
        ]);

        foreach ($request->items as $item) {
            $base       = (float) ($item['base_salary'] ?? 0);
            $bonuses    = (float) ($item['bonuses'] ?? 0);
            $deductions = (float) ($item['deductions'] ?? 0);

            $run->items()->create([
                'employee_id' => $item['employee_id'],
                'base_salary' => $base,
                'bonuses'     => $bonuses,
                'deductions'  => $deductions,
                'net_salary'  => round($base + $bonuses - $deductions, 2),
                'notes'       => $item['notes'] ?? null,
            ]);
        }

        return back()->with('success', 'Payroll run created.');
    }

    public function approve(PayrollRun $payroll)
    {
        abort_unless(auth()->user()->hasPermission('approve_payroll'), 403);
        $payroll->update([
            'status'      => PayrollStatus::Approved->value,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Payroll run approved.');
    }

    public function markPaid(PayrollRun $payroll)
    {
        abort_unless(auth()->user()->hasPermission('mark_payroll_paid'), 403);
        $payroll->update(['status' => PayrollStatus::Paid->value]);

        return back()->with('success', 'Payroll run marked as paid.');
    }

    public function destroy(PayrollRun $payroll)
    {
        abort_unless(auth()->user()->hasPermission('delete_payroll'), 403);
        $payroll->delete();

        return back()->with('success', 'Payroll run deleted.');
    }
}

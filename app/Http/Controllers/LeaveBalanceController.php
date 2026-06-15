<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use Illuminate\Http\Request;

class LeaveBalanceController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('view_leave_balances'), 403);
        $query = LeaveBalance::with(['employee.user', 'leaveType']);

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }
        if ($request->filled('leave_type_id')) {
            $query->where('leave_type_id', $request->leave_type_id);
        }
        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }

        $balances   = $query->orderBy('year', 'desc')->paginate(20)->withQueryString();
        $employees  = Employee::with('user')->get();
        $leaveTypes = LeaveType::orderBy('name')->get();
        $years      = range(now()->year + 1, now()->year - 3);

        return view('leave-balances.index', compact('balances', 'employees', 'leaveTypes', 'years'));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('create_leave_balances'), 403);
        $request->validate([
            'employee_id'    => ['required', 'exists:employees,id'],
            'leave_type_id'  => ['required', 'exists:leave_types,id'],
            'year'           => ['required', 'integer', 'min:2000'],
            'allocated_days' => ['required', 'numeric', 'min:0'],
            'used_days'      => ['nullable', 'numeric', 'min:0'],
        ]);

        LeaveBalance::updateOrCreate(
            [
                'employee_id'   => $request->employee_id,
                'leave_type_id' => $request->leave_type_id,
                'year'          => $request->year,
            ],
            [
                'allocated_days' => $request->allocated_days,
                'used_days'      => $request->used_days ?? 0,
            ]
        );

        return back()->with('success', 'Leave balance saved.');
    }

    public function update(Request $request, LeaveBalance $leaveBalance)
    {
        abort_unless(auth()->user()->hasPermission('edit_leave_balances'), 403);
        $request->validate([
            'allocated_days' => ['required', 'numeric', 'min:0'],
            'used_days'      => ['nullable', 'numeric', 'min:0'],
        ]);

        $leaveBalance->update([
            'allocated_days' => $request->allocated_days,
            'used_days'      => $request->used_days ?? 0,
        ]);

        return back()->with('success', 'Leave balance updated.');
    }

    public function destroy(LeaveBalance $leaveBalance)
    {
        abort_unless(auth()->user()->hasPermission('delete_leave_balances'), 403);
        $leaveBalance->delete();

        return back()->with('success', 'Leave balance deleted.');
    }
}

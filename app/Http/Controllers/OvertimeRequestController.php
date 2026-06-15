<?php

namespace App\Http\Controllers;

use App\Enums\OvertimeStatus;
use App\Models\Employee;
use App\Models\OvertimeRequest;
use App\Notifications\OvertimeStatusNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;

class OvertimeRequestController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('view_overtime_requests'), 403);
        $canViewAll = auth()->user()->hasPermission('view_all_overtime_requests');
        $tab        = ($canViewAll && $request->get('tab') === 'all') ? 'all' : 'mine';

        $query = OvertimeRequest::with(['employee.user', 'approver']);

        if ($tab === 'mine') {
            $employee = Employee::where('user_id', auth()->id())->first();
            $query->where('employee_id', $employee?->id ?? 0);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($tab === 'all' && $request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        $overtimeRequests = $query->latest('date')->paginate(15)->withQueryString();
        $employees        = Employee::with('user')->get();
        $statuses         = OvertimeStatus::cases();
        $canApprove       = auth()->user()->hasPermission('approve_overtime_requests');

        return view('overtime-requests.index', compact(
            'overtimeRequests', 'employees', 'statuses', 'tab', 'canViewAll', 'canApprove'
        ));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('create_overtime_requests'), 403);
        $request->validate([
            'date'       => ['required', 'date'],
            'start_time' => ['required', 'string'],
            'end_time'   => ['required', 'string'],
            'reason'     => ['nullable', 'string'],
            'multiplier' => ['nullable', 'numeric', 'min:1'],
        ]);

        $employee = Employee::where('user_id', auth()->id())->first();
        if (!$employee) {
            return back()->withErrors(['employee' => 'No employee record is linked to your account.']);
        }

        $start = Carbon::parse($request->date . ' ' . $request->start_time);
        $end   = Carbon::parse($request->date . ' ' . $request->end_time);
        $hours = round($start->diffInMinutes($end) / 60, 2);

        OvertimeRequest::create([
            'employee_id' => $employee->id,
            'date'        => $request->date,
            'start_time'  => $request->start_time,
            'end_time'    => $request->end_time,
            'hours'       => $hours,
            'multiplier'  => $request->multiplier ?? 1.5,
            'reason'      => $request->reason,
            'status'      => OvertimeStatus::Pending->value,
        ]);

        return back()->with('success', 'Overtime request submitted.');
    }

    public function update(Request $request, OvertimeRequest $overtimeRequest)
    {
        abort_unless(auth()->user()->hasPermission('edit_overtime_requests'), 403);
        $request->validate([
            'date'       => ['required', 'date'],
            'start_time' => ['required', 'string'],
            'end_time'   => ['required', 'string'],
            'reason'     => ['nullable', 'string'],
            'multiplier' => ['nullable', 'numeric', 'min:1'],
        ]);

        $start = Carbon::parse($request->date . ' ' . $request->start_time);
        $end   = Carbon::parse($request->date . ' ' . $request->end_time);
        $hours = round($start->diffInMinutes($end) / 60, 2);

        $overtimeRequest->update([
            'date'       => $request->date,
            'start_time' => $request->start_time,
            'end_time'   => $request->end_time,
            'hours'      => $hours,
            'multiplier' => $request->multiplier ?? 1.5,
            'reason'     => $request->reason,
            'status'     => OvertimeStatus::Pending->value,
        ]);

        return back()->with('success', 'Overtime request updated.');
    }

    public function approve(OvertimeRequest $overtimeRequest)
    {
        abort_unless(auth()->user()->hasPermission('approve_overtime_requests'), 403);
        $overtimeRequest->update([
            'status'      => OvertimeStatus::Approved->value,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        $overtimeRequest->load('employee.user');
        $overtimeRequest->employee?->user?->notify(new OvertimeStatusNotification($overtimeRequest));

        return back()->with('success', 'Overtime request approved.');
    }

    public function reject(OvertimeRequest $overtimeRequest)
    {
        abort_unless(auth()->user()->hasPermission('approve_overtime_requests'), 403);
        $overtimeRequest->update([
            'status'      => OvertimeStatus::Rejected->value,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        $overtimeRequest->load('employee.user');
        $overtimeRequest->employee?->user?->notify(new OvertimeStatusNotification($overtimeRequest));

        return back()->with('success', 'Overtime request rejected.');
    }

    public function destroy(OvertimeRequest $overtimeRequest)
    {
        abort_unless(auth()->user()->hasPermission('delete_overtime_requests'), 403);
        $overtimeRequest->delete();

        return back()->with('success', 'Overtime request deleted.');
    }
}

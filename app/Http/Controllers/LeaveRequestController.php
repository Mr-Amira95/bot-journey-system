<?php

namespace App\Http\Controllers;

use App\Enums\LeaveDurationType;
use App\Enums\LeaveRequestStatus;
use App\Models\Employee;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Notifications\LeaveStatusNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LeaveRequestController extends Controller
{
    private const HOURS_PER_DAY = 8;

    private function validationRules(): array
    {
        return [
            'leave_type_id' => ['required', 'exists:leave_types,id'],
            'duration_type' => ['required', 'in:full_day,hourly'],
            'start_date'    => ['required', 'date'],
            'end_date'      => ['required_if:duration_type,full_day', 'nullable', 'date', 'after_or_equal:start_date'],
            'start_time'    => ['required_if:duration_type,hourly', 'nullable', 'date_format:H:i'],
            'end_time'      => ['required_if:duration_type,hourly', 'nullable', 'date_format:H:i', 'after:start_time'],
            'reason'        => ['nullable', 'string'],
        ];
    }

    private function durationFields(Request $request): array
    {
        if ($request->duration_type === LeaveDurationType::Hourly->value) {
            $start      = Carbon::parse($request->start_date.' '.$request->start_time);
            $end        = Carbon::parse($request->start_date.' '.$request->end_time);
            $totalHours = round($start->diffInMinutes($end) / 60, 2);

            return [
                'duration_type' => LeaveDurationType::Hourly->value,
                'start_date'    => $request->start_date,
                'end_date'      => $request->start_date,
                'start_time'    => $request->start_time,
                'end_time'      => $request->end_time,
                'total_hours'   => $totalHours,
                'total_days'    => round($totalHours / self::HOURS_PER_DAY, 2),
            ];
        }

        $totalDays = Carbon::parse($request->start_date)
            ->diffInWeekdays(Carbon::parse($request->end_date)) + 1;

        return [
            'duration_type' => LeaveDurationType::FullDay->value,
            'start_date'    => $request->start_date,
            'end_date'      => $request->end_date,
            'start_time'    => null,
            'end_time'      => null,
            'total_hours'   => null,
            'total_days'    => $totalDays,
        ];
    }

    public function index(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('view_leave_requests'), 403);
        $canViewAll = auth()->user()->hasPermission('view_all_leave_requests');
        $tab        = ($canViewAll && $request->get('tab') === 'all') ? 'all' : 'mine';

        $query = LeaveRequest::with(['employee.user', 'leaveType', 'approver']);

        if ($tab === 'mine') {
            $employee = Employee::where('user_id', auth()->id())->first();
            $query->where('employee_id', $employee?->id ?? 0);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('leave_type_id')) {
            $query->where('leave_type_id', $request->leave_type_id);
        }
        if ($tab === 'all' && $request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        $leaveRequests = $query->latest('start_date')->paginate(15)->withQueryString();
        $leaveTypes    = LeaveType::orderBy('name')->get();
        $employees     = Employee::with('user')->get();
        $statuses      = LeaveRequestStatus::cases();
        $canApprove    = auth()->user()->hasPermission('approve_leave_requests');

        return view('leave-requests.index', compact(
            'leaveRequests', 'leaveTypes', 'employees', 'statuses', 'tab', 'canViewAll', 'canApprove'
        ));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('create_leave_requests'), 403);
        $request->validate($this->validationRules());

        $employee = Employee::where('user_id', auth()->id())->first();
        if (!$employee) {
            return back()->withErrors(['employee' => 'No employee record is linked to your account.']);
        }

        $leaveType = LeaveType::findOrFail($request->leave_type_id);
        $status    = $leaveType->requires_approval
            ? LeaveRequestStatus::Pending->value
            : LeaveRequestStatus::Approved->value;

        LeaveRequest::create(array_merge(
            $this->durationFields($request),
            [
                'employee_id'   => $employee->id,
                'leave_type_id' => $request->leave_type_id,
                'reason'        => $request->reason,
                'status'        => $status,
            ]
        ));

        return back()->with('success', 'Leave request submitted.');
    }

    public function update(Request $request, LeaveRequest $leaveRequest)
    {
        abort_unless(auth()->user()->hasPermission('edit_leave_requests'), 403);
        $request->validate($this->validationRules());

        $leaveRequest->update(array_merge(
            $this->durationFields($request),
            [
                'leave_type_id' => $request->leave_type_id,
                'reason'        => $request->reason,
                'status'        => LeaveRequestStatus::Pending->value,
            ]
        ));

        return back()->with('success', 'Leave request updated.');
    }

    public function approve(LeaveRequest $leaveRequest)
    {
        abort_unless(auth()->user()->hasPermission('approve_leave_requests'), 403);
        $leaveRequest->update([
            'status'      => LeaveRequestStatus::Approved->value,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        LeaveBalance::where('employee_id', $leaveRequest->employee_id)
            ->where('leave_type_id', $leaveRequest->leave_type_id)
            ->where('year', $leaveRequest->start_date->year)
            ->increment('used_days', $leaveRequest->total_days);

        $leaveRequest->load('employee.user', 'leaveType');
        $leaveRequest->employee?->user?->notify(new LeaveStatusNotification($leaveRequest));

        return back()->with('success', 'Leave request approved.');
    }

    public function reject(Request $request, LeaveRequest $leaveRequest)
    {
        abort_unless(auth()->user()->hasPermission('approve_leave_requests'), 403);
        $request->validate([
            'rejection_reason' => ['nullable', 'string'],
        ]);

        $leaveRequest->update([
            'status'           => LeaveRequestStatus::Rejected->value,
            'approved_by'      => auth()->id(),
            'approved_at'      => now(),
            'rejection_reason' => $request->rejection_reason,
        ]);

        $leaveRequest->load('employee.user', 'leaveType');
        $leaveRequest->employee?->user?->notify(new LeaveStatusNotification($leaveRequest));

        return back()->with('success', 'Leave request rejected.');
    }

    public function destroy(LeaveRequest $leaveRequest)
    {
        abort_unless(auth()->user()->hasPermission('delete_leave_requests'), 403);
        $leaveRequest->delete();

        return back()->with('success', 'Leave request deleted.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Enums\AttendanceType;
use App\Models\EmployeeAttendance;
use App\Models\User;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('view_attendance'), 403);
        $canViewAll = auth()->user()->hasPermission('view_all_attendance');
        $tab        = ($canViewAll && $request->input('tab') === 'all') ? 'all' : 'mine';

        $query = EmployeeAttendance::with('user')->latest('time_date');

        if ($tab === 'mine') {
            $query->where('user_id', auth()->id());
        }

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }
        if ($request->filled('user_id') && $canViewAll) {
            $query->where('user_id', $request->input('user_id'));
        }
        if ($request->filled('date_from')) {
            $query->whereDate('time_date', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('time_date', '<=', $request->input('date_to'));
        }

        $records   = $query->paginate(20)->withQueryString();
        $types     = AttendanceType::cases();
        $users     = User::orderBy('name')->get();
        $canManage = auth()->user()->hasPermission('create_attendance');

        $todayLast = EmployeeAttendance::where('user_id', auth()->id())
            ->whereDate('time_date', today())
            ->latest('time_date')
            ->first();

        return view('attendance.index', compact(
            'records', 'types', 'users', 'tab', 'canViewAll', 'canManage', 'todayLast'
        ));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('create_attendance'), 403);
        $request->validate([
            'user_id'   => ['required', 'exists:users,id'],
            'type'      => ['required', 'in:check_in,check_out,break_start,break_end'],
            'time_date' => ['required', 'date'],
            'notes'     => ['nullable', 'string'],
        ]);

        EmployeeAttendance::create($request->only('user_id', 'type', 'time_date', 'notes'));

        return back()->with('success', 'Attendance record added.');
    }

    public function update(Request $request, EmployeeAttendance $attendance)
    {
        abort_unless(auth()->user()->hasPermission('create_attendance'), 403);
        $request->validate([
            'user_id'   => ['required', 'exists:users,id'],
            'type'      => ['required', 'in:check_in,check_out,break_start,break_end'],
            'time_date' => ['required', 'date'],
            'notes'     => ['nullable', 'string'],
        ]);

        $attendance->update($request->only('user_id', 'type', 'time_date', 'notes'));

        return back()->with('success', 'Attendance record updated.');
    }

    public function destroy(EmployeeAttendance $attendance)
    {
        abort_unless(auth()->user()->hasPermission('create_attendance'), 403);
        $attendance->delete();

        return back()->with('success', 'Attendance record deleted.');
    }

    public function clockIn(Request $request)
    {
        EmployeeAttendance::create([
            'user_id'   => auth()->id(),
            'type'      => AttendanceType::CheckIn->value,
            'time_date' => now(),
            'notes'     => $request->input('notes'),
        ]);

        return back()->with('success', 'Checked in successfully.');
    }

    public function clockOut(Request $request)
    {
        EmployeeAttendance::create([
            'user_id'   => auth()->id(),
            'type'      => AttendanceType::CheckOut->value,
            'time_date' => now(),
            'notes'     => $request->input('notes'),
        ]);

        return back()->with('success', 'Checked out successfully.');
    }

    public function breakStart()
    {
        EmployeeAttendance::create([
            'user_id'   => auth()->id(),
            'type'      => AttendanceType::BreakStart->value,
            'time_date' => now(),
        ]);

        return back()->with('success', 'Break started.');
    }

    public function breakEnd()
    {
        EmployeeAttendance::create([
            'user_id'   => auth()->id(),
            'type'      => AttendanceType::BreakEnd->value,
            'time_date' => now(),
        ]);

        return back()->with('success', 'Break ended.');
    }
}

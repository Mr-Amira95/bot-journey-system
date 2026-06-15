<?php

namespace App\Http\Controllers;

use App\Models\WorkSchedule;
use Illuminate\Http\Request;

class WorkScheduleController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('view_work_schedules'), 403);
        $query = WorkSchedule::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $schedules = $query->latest()->paginate(15)->withQueryString();

        return view('work-schedules.index', compact('schedules'));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('create_work_schedules'), 403);
        $request->validate([
            'name'                   => ['required', 'string', 'max:255'],
            'start_time'             => ['required', 'string'],
            'end_time'               => ['required', 'string'],
            'working_days'           => ['required', 'array', 'min:1'],
            'working_days.*'         => ['string'],
            'break_duration_minutes' => ['nullable', 'integer', 'min:0'],
            'description'            => ['nullable', 'string'],
        ]);

        WorkSchedule::create([
            'name'                   => $request->name,
            'start_time'             => $request->start_time,
            'end_time'               => $request->end_time,
            'working_days'           => $request->working_days,
            'break_duration_minutes' => $request->break_duration_minutes ?? 60,
            'description'            => $request->description,
        ]);

        return back()->with('success', 'Work schedule created.');
    }

    public function update(Request $request, WorkSchedule $workSchedule)
    {
        abort_unless(auth()->user()->hasPermission('edit_work_schedules'), 403);
        $request->validate([
            'name'                   => ['required', 'string', 'max:255'],
            'start_time'             => ['required', 'string'],
            'end_time'               => ['required', 'string'],
            'working_days'           => ['required', 'array', 'min:1'],
            'working_days.*'         => ['string'],
            'break_duration_minutes' => ['nullable', 'integer', 'min:0'],
            'description'            => ['nullable', 'string'],
        ]);

        $workSchedule->update([
            'name'                   => $request->name,
            'start_time'             => $request->start_time,
            'end_time'               => $request->end_time,
            'working_days'           => $request->working_days,
            'break_duration_minutes' => $request->break_duration_minutes ?? 60,
            'description'            => $request->description,
        ]);

        return back()->with('success', 'Work schedule updated.');
    }

    public function destroy(WorkSchedule $workSchedule)
    {
        abort_unless(auth()->user()->hasPermission('delete_work_schedules'), 403);
        $workSchedule->delete();

        return back()->with('success', 'Work schedule deleted.');
    }
}

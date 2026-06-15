<?php

namespace App\Http\Controllers;

use App\Models\LeaveType;
use Illuminate\Http\Request;

class LeaveTypeController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('view_leave_types'), 403);
        $query = LeaveType::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $leaveTypes = $query->latest()->paginate(20)->withQueryString();

        return view('leave-types.index', compact('leaveTypes'));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('create_leave_types'), 403);
        $request->validate([
            'name'              => ['required', 'string', 'max:255'],
            'max_days_per_year' => ['nullable', 'integer', 'min:1'],
            'color'             => ['nullable', 'string', 'max:20'],
        ]);

        LeaveType::create([
            'name'              => $request->name,
            'is_paid'           => $request->has('is_paid'),
            'max_days_per_year' => $request->max_days_per_year,
            'requires_approval' => $request->has('requires_approval'),
            'color'             => $request->color,
        ]);

        return back()->with('success', 'Leave type created.');
    }

    public function update(Request $request, LeaveType $leaveType)
    {
        abort_unless(auth()->user()->hasPermission('edit_leave_types'), 403);
        $request->validate([
            'name'              => ['required', 'string', 'max:255'],
            'max_days_per_year' => ['nullable', 'integer', 'min:1'],
            'color'             => ['nullable', 'string', 'max:20'],
        ]);

        $leaveType->update([
            'name'              => $request->name,
            'is_paid'           => $request->has('is_paid'),
            'max_days_per_year' => $request->max_days_per_year,
            'requires_approval' => $request->has('requires_approval'),
            'color'             => $request->color,
        ]);

        return back()->with('success', 'Leave type updated.');
    }

    public function destroy(LeaveType $leaveType)
    {
        abort_unless(auth()->user()->hasPermission('delete_leave_types'), 403);
        $leaveType->delete();

        return back()->with('success', 'Leave type deleted.');
    }
}

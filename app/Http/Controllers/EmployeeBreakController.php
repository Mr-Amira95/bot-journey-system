<?php

namespace App\Http\Controllers;

use App\Enums\BreakType;
use App\Models\EmployeeBreak;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EmployeeBreakController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('view_employee_breaks'), 403);
        $canViewAll = auth()->user()->hasPermission('view_all_employee_breaks');
        $tab        = ($canViewAll && $request->get('tab') === 'all') ? 'all' : 'mine';

        $query = EmployeeBreak::with('user');

        if ($tab === 'mine') {
            $query->where('user_id', auth()->id());
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($tab === 'all' && $request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $breaks     = $query->latest('started_at')->paginate(15)->withQueryString();
        $users      = User::orderBy('name')->get();
        $breakTypes = BreakType::cases();

        return view('employee-breaks.index', compact('breaks', 'users', 'breakTypes', 'tab', 'canViewAll'));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('create_employee_breaks'), 403);
        $request->validate([
            'started_at' => ['required', 'date'],
            'ended_at'   => ['nullable', 'date', 'after:started_at'],
            'type'       => ['required', Rule::enum(BreakType::class)],
            'notes'      => ['nullable', 'string'],
        ]);

        EmployeeBreak::create([
            'user_id'    => auth()->id(),
            'started_at' => $request->started_at,
            'ended_at'   => $request->ended_at,
            'type'       => $request->type,
            'notes'      => $request->notes,
        ]);

        return back()->with('success', 'Break logged.');
    }

    public function update(Request $request, EmployeeBreak $employeeBreak)
    {
        abort_unless(auth()->user()->hasPermission('create_employee_breaks'), 403);
        $request->validate([
            'started_at' => ['required', 'date'],
            'ended_at'   => ['nullable', 'date', 'after:started_at'],
            'type'       => ['required', Rule::enum(BreakType::class)],
            'notes'      => ['nullable', 'string'],
        ]);

        $employeeBreak->update([
            'started_at' => $request->started_at,
            'ended_at'   => $request->ended_at,
            'type'       => $request->type,
            'notes'      => $request->notes,
        ]);

        return back()->with('success', 'Break updated.');
    }

    public function destroy(EmployeeBreak $employeeBreak)
    {
        abort_unless(auth()->user()->hasPermission('delete_employee_breaks'), 403);
        $employeeBreak->delete();

        return back()->with('success', 'Break deleted.');
    }
}

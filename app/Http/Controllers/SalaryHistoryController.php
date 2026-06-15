<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\SalaryHistory;
use Illuminate\Http\Request;

class SalaryHistoryController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('view_salary_histories'), 403);
        $query = SalaryHistory::with(['employee.user', 'changedBy']);

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }
        if ($request->filled('search')) {
            $query->whereHas('employee.user', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            });
        }

        $histories = $query->latest('effective_date')->paginate(15)->withQueryString();
        $employees = Employee::with('user')->get();

        return view('salary-histories.index', compact('histories', 'employees'));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('create_salary_histories'), 403);
        $request->validate([
            'employee_id'    => ['required', 'exists:employees,id'],
            'salary'         => ['nullable', 'numeric', 'min:0'],
            'hourly_rate'    => ['nullable', 'numeric', 'min:0'],
            'effective_date' => ['required', 'date'],
            'end_date'       => ['nullable', 'date', 'after_or_equal:effective_date'],
            'notes'          => ['nullable', 'string'],
        ]);

        SalaryHistory::create(array_merge(
            $request->only('employee_id', 'salary', 'hourly_rate', 'effective_date', 'end_date', 'notes'),
            ['changed_by' => auth()->id()]
        ));

        return back()->with('success', 'Salary record added.');
    }

    public function update(Request $request, SalaryHistory $salaryHistory)
    {
        abort_unless(auth()->user()->hasPermission('edit_salary_histories'), 403);
        $request->validate([
            'employee_id'    => ['required', 'exists:employees,id'],
            'salary'         => ['nullable', 'numeric', 'min:0'],
            'hourly_rate'    => ['nullable', 'numeric', 'min:0'],
            'effective_date' => ['required', 'date'],
            'end_date'       => ['nullable', 'date', 'after_or_equal:effective_date'],
            'notes'          => ['nullable', 'string'],
        ]);

        $salaryHistory->update($request->only(
            'employee_id', 'salary', 'hourly_rate', 'effective_date', 'end_date', 'notes'
        ));

        return back()->with('success', 'Salary record updated.');
    }

    public function destroy(SalaryHistory $salaryHistory)
    {
        abort_unless(auth()->user()->hasPermission('delete_salary_histories'), 403);
        $salaryHistory->delete();

        return back()->with('success', 'Salary record deleted.');
    }
}

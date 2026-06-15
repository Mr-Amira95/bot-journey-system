<?php

namespace App\Http\Controllers;

use App\Enums\RecurringFrequency;
use App\Models\ExpenseCategory;
use App\Models\Project;
use App\Models\RecurringExpenseTemplate;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RecurringExpenseController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('view_recurring_expenses'), 403);
        $query = RecurringExpenseTemplate::with(['category', 'project', 'createdBy']);

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('frequency')) {
            $query->where('frequency', $request->frequency);
        }
        if ($request->filled('active')) {
            $query->where('is_active', $request->active === '1');
        }

        $templates   = $query->latest()->paginate(15)->withQueryString();
        $categories  = ExpenseCategory::orderBy('type')->get();
        $projects    = Project::orderBy('name')->get();
        $frequencies = RecurringFrequency::cases();

        return view('recurring-expenses.index', compact('templates', 'categories', 'projects', 'frequencies'));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('create_recurring_expenses'), 403);
        $request->validate([
            'title'         => ['required', 'string', 'max:255'],
            'description'   => ['nullable', 'string'],
            'category_id'   => ['required', 'exists:expense_categories,id'],
            'project_id'    => ['nullable', 'exists:projects,id'],
            'amount'        => ['required', 'numeric', 'min:0'],
            'frequency'     => ['required', Rule::enum(RecurringFrequency::class)],
            'start_date'    => ['required', 'date'],
            'end_date'      => ['nullable', 'date', 'after_or_equal:start_date'],
            'next_run_date' => ['required', 'date'],
        ]);

        RecurringExpenseTemplate::create(array_merge(
            $request->only('title', 'description', 'category_id', 'project_id', 'amount',
                           'frequency', 'start_date', 'end_date', 'next_run_date'),
            ['is_active' => true, 'created_by' => auth()->id()]
        ));

        return back()->with('success', 'Recurring expense created.');
    }

    public function update(Request $request, RecurringExpenseTemplate $recurringExpense)
    {
        abort_unless(auth()->user()->hasPermission('edit_recurring_expenses'), 403);
        $request->validate([
            'title'         => ['required', 'string', 'max:255'],
            'description'   => ['nullable', 'string'],
            'category_id'   => ['required', 'exists:expense_categories,id'],
            'project_id'    => ['nullable', 'exists:projects,id'],
            'amount'        => ['required', 'numeric', 'min:0'],
            'frequency'     => ['required', Rule::enum(RecurringFrequency::class)],
            'start_date'    => ['required', 'date'],
            'end_date'      => ['nullable', 'date', 'after_or_equal:start_date'],
            'next_run_date' => ['required', 'date'],
            'is_active'     => ['sometimes', 'boolean'],
        ]);

        $recurringExpense->update($request->only(
            'title', 'description', 'category_id', 'project_id', 'amount',
            'frequency', 'start_date', 'end_date', 'next_run_date', 'is_active'
        ));

        return back()->with('success', 'Recurring expense updated.');
    }

    public function destroy(RecurringExpenseTemplate $recurringExpense)
    {
        abort_unless(auth()->user()->hasPermission('delete_recurring_expenses'), 403);
        $recurringExpense->delete();

        return back()->with('success', 'Recurring expense deleted.');
    }
}

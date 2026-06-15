<?php

namespace App\Http\Controllers;

use App\Enums\ExpenseStatus;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Project;
use App\Models\User;
use App\Notifications\ExpenseStatusNotification;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('view_expenses'), 403);
        $viewingAll = auth()->user()->hasPermission('view_all_expenses');
        $tab        = ($viewingAll && $request->get('tab') === 'all') ? 'all' : 'mine';

        $query = Expense::with(['category', 'project', 'paidBy']);

        if ($tab === 'mine') {
            $query->where('paid_by', auth()->id());
        }

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($tab === 'all' && $request->filled('paid_by_user')) {
            $query->where('paid_by', $request->paid_by_user);
        }

        $expenses   = $query->latest('expense_date')->paginate(15)->withQueryString();
        $categories = ExpenseCategory::orderBy('type')->get();
        $projects   = Project::orderBy('name')->get();
        $users      = User::orderBy('name')->get();
        $statuses   = ExpenseStatus::cases();

        return view('expenses.index', compact('expenses', 'categories', 'projects', 'users', 'statuses', 'viewingAll', 'tab'));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('create_expenses'), 403);
        $request->validate([
            'title'        => ['required', 'string', 'max:255'],
            'description'  => ['nullable', 'string'],
            'category_id'  => ['required', 'exists:expense_categories,id'],
            'project_id'   => ['nullable', 'exists:projects,id'],
            'paid_by'      => ['required', 'exists:users,id'],
            'amount'       => ['required', 'numeric', 'min:0'],
            'expense_date' => ['required', 'date'],
            'status'       => ['required', Rule::enum(ExpenseStatus::class)],
        ]);

        Expense::create($request->only(
            'title', 'description', 'category_id', 'project_id', 'paid_by', 'amount', 'expense_date', 'status'
        ));

        return back()->with('success', 'Expense recorded.');
    }

    public function update(Request $request, Expense $expense)
    {
        abort_unless(auth()->user()->hasPermission('edit_expenses'), 403);
        $request->validate([
            'title'        => ['required', 'string', 'max:255'],
            'description'  => ['nullable', 'string'],
            'category_id'  => ['required', 'exists:expense_categories,id'],
            'project_id'   => ['nullable', 'exists:projects,id'],
            'paid_by'      => ['required', 'exists:users,id'],
            'amount'       => ['required', 'numeric', 'min:0'],
            'expense_date' => ['required', 'date'],
            'status'       => ['required', Rule::enum(ExpenseStatus::class)],
        ]);

        $previousStatus = $expense->status->value ?? $expense->status;

        $expense->update($request->only(
            'title', 'description', 'category_id', 'project_id', 'paid_by', 'amount', 'expense_date', 'status'
        ));

        $newStatus = $expense->fresh()->status->value ?? $expense->status;
        $notifiableStatuses = [ExpenseStatus::Approved->value, ExpenseStatus::Rejected->value];

        if ($previousStatus !== $newStatus && in_array($newStatus, $notifiableStatuses)) {
            User::find($expense->paid_by)?->notify(new ExpenseStatusNotification($expense->fresh()));
        }

        return back()->with('success', 'Expense updated.');
    }

    public function destroy(Expense $expense)
    {
        abort_unless(auth()->user()->hasPermission('delete_expenses'), 403);
        $expense->delete();

        return back()->with('success', 'Expense deleted.');
    }
}

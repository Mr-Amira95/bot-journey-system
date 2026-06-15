<?php

namespace App\Http\Controllers;

use App\Enums\ExpenseCategoryType;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ExpenseCategoryController extends Controller
{
    public function index()
    {
        abort_unless(auth()->user()->hasPermission('view_expense_categories'), 403);
        $categories = ExpenseCategory::withCount('expenses')->latest()->paginate(20);
        $types      = ExpenseCategoryType::cases();

        return view('expense-categories.index', compact('categories', 'types'));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('create_expense_categories'), 403);
        $request->validate([
            'type'  => ['required', Rule::enum(ExpenseCategoryType::class)],
            'title' => ['nullable', 'string', 'max:255'],
        ]);

        ExpenseCategory::create($request->only('type', 'title'));

        return back()->with('success', 'Category created.');
    }

    public function update(Request $request, ExpenseCategory $category)
    {
        abort_unless(auth()->user()->hasPermission('edit_expense_categories'), 403);
        $request->validate([
            'type'  => ['required', Rule::enum(ExpenseCategoryType::class)],
            'title' => ['nullable', 'string', 'max:255'],
        ]);

        $category->update($request->only('type', 'title'));

        return back()->with('success', 'Category updated.');
    }

    public function destroy(ExpenseCategory $category)
    {
        abort_unless(auth()->user()->hasPermission('delete_expense_categories'), 403);
        if ($category->expenses()->exists()) {
            return back()->with('error', 'Cannot delete a category that has expenses linked to it.');
        }

        $category->delete();

        return back()->with('success', 'Category deleted.');
    }
}

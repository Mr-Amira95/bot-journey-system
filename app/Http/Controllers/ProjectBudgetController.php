<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectBudgetLine;
use Illuminate\Http\Request;

class ProjectBudgetController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('view_project_budgets'), 403);
        $query = ProjectBudgetLine::with('project');

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }
        if ($request->filled('search')) {
            $query->where('category', 'like', '%' . $request->search . '%');
        }

        $budgetLines = $query->latest()->paginate(15)->withQueryString();
        $projects    = Project::orderBy('name')->get();

        return view('project-budgets.index', compact('budgetLines', 'projects'));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('create_project_budgets'), 403);
        $request->validate([
            'project_id'      => ['required', 'exists:projects,id'],
            'category'        => ['required', 'string', 'max:255'],
            'budgeted_amount' => ['required', 'numeric', 'min:0'],
            'notes'           => ['nullable', 'string'],
        ]);

        ProjectBudgetLine::create($request->only('project_id', 'category', 'budgeted_amount', 'notes'));

        return back()->with('success', 'Budget line added.');
    }

    public function update(Request $request, ProjectBudgetLine $projectBudget)
    {
        abort_unless(auth()->user()->hasPermission('edit_project_budgets'), 403);
        $request->validate([
            'project_id'      => ['required', 'exists:projects,id'],
            'category'        => ['required', 'string', 'max:255'],
            'budgeted_amount' => ['required', 'numeric', 'min:0'],
            'notes'           => ['nullable', 'string'],
        ]);

        $projectBudget->update($request->only('project_id', 'category', 'budgeted_amount', 'notes'));

        return back()->with('success', 'Budget line updated.');
    }

    public function destroy(ProjectBudgetLine $projectBudget)
    {
        abort_unless(auth()->user()->hasPermission('delete_project_budgets'), 403);
        $projectBudget->delete();

        return back()->with('success', 'Budget line deleted.');
    }
}

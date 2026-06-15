<?php

namespace App\Http\Controllers;

use App\Http\Requests\DepartmentRequest;
use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('view_departments'), 403);
        $query = Department::withCount('employees');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $departments = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('departments.index', compact('departments'));
    }

    public function store(DepartmentRequest $request)
    {
        abort_unless(auth()->user()->hasPermission('create_departments'), 403);
        Department::create($request->validated());

        return redirect()->route('departments.index')
            ->with('success', 'Department created successfully.');
    }

    public function update(DepartmentRequest $request, Department $department)
    {
        abort_unless(auth()->user()->hasPermission('edit_departments'), 403);
        $department->update($request->validated());

        return redirect()->route('departments.index')
            ->with('success', 'Department updated successfully.');
    }

    public function destroy(Department $department)
    {
        abort_unless(auth()->user()->hasPermission('delete_departments'), 403);
        if ($department->employees()->exists()) {
            return redirect()->route('departments.index')
                ->with('error', 'Cannot delete a department that has employees assigned to it.');
        }

        $department->delete();

        return redirect()->route('departments.index')
            ->with('success', 'Department deleted successfully.');
    }
}

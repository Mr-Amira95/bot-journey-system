<?php

namespace App\Http\Controllers;

use App\Http\Requests\RoleRequest;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('view_roles'), 403);
        $roles = Role::with('permissions')
            ->withCount(['permissions', 'users'])
            ->paginate(15);

        $permissions = Permission::orderBy('module')
            ->orderBy('name')
            ->get()
            ->groupBy('module');

        return view('roles.index', compact('roles', 'permissions'));
    }

    public function store(RoleRequest $request)
    {
        abort_unless(auth()->user()->hasPermission('create_roles'), 403);
        Role::create($request->validated());

        return redirect()->route('roles.index')
            ->with('success', 'Role created successfully.');
    }

    public function update(RoleRequest $request, Role $role)
    {
        abort_unless(auth()->user()->hasPermission('edit_roles'), 403);
        $role->update($request->validated());

        return redirect()->route('roles.index')
            ->with('success', 'Role updated successfully.');
    }

    public function destroy(Role $role)
    {
        abort_unless(auth()->user()->hasPermission('delete_roles'), 403);
        if ($role->users()->exists()) {
            return redirect()->route('roles.index')
                ->with('error', 'Cannot delete a role that is assigned to users.');
        }

        $role->delete();

        return redirect()->route('roles.index')
            ->with('success', 'Role deleted successfully.');
    }

    public function updatePermissions(Request $request, Role $role)
    {
        abort_unless(auth()->user()->hasPermission('edit_roles'), 403);
        $request->validate([
            'permissions'   => ['nullable', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ]);

        $role->permissions()->sync($request->input('permissions', []));

        return redirect()->route('roles.index')
            ->with('success', "Permissions for \"{$role->name}\" updated.");
    }
}

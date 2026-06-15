<?php

namespace App\Http\Controllers;

use App\Enums\EmployeeType;
use App\Mail\EmployeeDocumentMail;
use App\Models\Department;
use App\Models\Employee;
use App\Models\ProjectMember;
use App\Models\Role;
use App\Models\User;
use App\Models\UserAttachment;
use App\Services\DocumentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('view_employees'), 403);

        $query = Employee::with(['user', 'department', 'manager.user']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $employees     = $query->paginate(15)->withQueryString();
        $departments   = Department::orderBy('name')->get();
        $employeeTypes = EmployeeType::cases();
        $managers      = Employee::with('user')->get();
        $roles         = Role::orderBy('name')->get();

        return view('employees.index', compact('employees', 'departments', 'employeeTypes', 'managers', 'roles'));
    }

    public function show(Employee $employee)
    {
        abort_unless(auth()->user()->hasPermission('view_employees'), 403);

        $employee->load(['user.attachments', 'department', 'manager.user', 'subordinates.user']);
        $departments      = Department::orderBy('name')->get();
        $employeeTypes    = EmployeeType::cases();
        $managers         = Employee::with('user')->where('id', '!=', $employee->id)->get();
        $employeeProjects = ProjectMember::where('user_id', $employee->user_id)
            ->with('project.client')
            ->get()
            ->pluck('project')
            ->filter();

        return view('employees.show', compact('employee', 'departments', 'employeeTypes', 'managers', 'employeeProjects'));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('create_employees'), 403);

        $validated = $request->validate([
            'name'              => ['required', 'string', 'max:255'],
            'email'             => ['required', 'email', 'unique:users,email'],
            'status'            => ['required', 'string', 'in:active,inactive,suspended'],
            'profile_image'     => ['nullable', 'image', 'max:2048'],
            'department_id'     => ['required', 'exists:departments,id'],
            'manager_id'        => ['nullable', 'exists:employees,id'],
            'position'          => ['required', 'string', 'max:255'],
            'hire_date'         => ['required', 'date', 'before_or_equal:today'],
            'type'              => ['required', Rule::enum(EmployeeType::class)],
            'salary'            => [
                'nullable', 'numeric', 'min:0',
                Rule::requiredIf(fn () => $request->input('type') === EmployeeType::ContractEmployee->value),
            ],
            'hourly_rate'       => [
                'nullable', 'numeric', 'min:0',
                Rule::requiredIf(fn () => $request->input('type') === EmployeeType::HourlyEmployee->value),
            ],
            'attachments'       => ['nullable', 'array', 'max:20'],
            'attachments.*.key' => ['nullable', 'string', 'max:255'],
            'attachments.*.file'=> ['nullable', 'file', 'max:10240'],
        ]);

        $profileImage = $request->hasFile('profile_image')
            ? $request->file('profile_image')->store('profile-images', 'public')
            : $this->generateAvatar($validated['name']);

        $user = User::create([
            'name'                 => $validated['name'],
            'email'                => $validated['email'],
            'password'             => Str::random(24),
            'status'               => $validated['status'],
            'profile_image'        => $profileImage,
            'must_change_password' => true,
        ]);

        if ($request->filled('role_id')) {
            $user->roles()->sync([$request->role_id]);
        }

        Employee::create([
            'user_id'       => $user->id,
            'department_id' => $validated['department_id'],
            'manager_id'    => $validated['manager_id'] ?? null,
            'position'      => $validated['position'],
            'hire_date'     => $validated['hire_date'],
            'type'          => $validated['type'],
            'salary'        => $validated['salary'] ?? null,
            'hourly_rate'   => $validated['hourly_rate'] ?? null,
        ]);

        $this->storeAttachments(
            $request->input('attachments', []),
            $request->file('attachments', []),
            $user,
            'employee-attachments'
        );

        $employee = Employee::where('user_id', $user->id)->first();
        app(DocumentService::class)->generateEmployeeDocuments($employee);

        Password::broker()->sendResetLink(['email' => $user->email]);

        return redirect()->route('employees.index')
            ->with('success', 'Employee created successfully. A link to set their password has been emailed to them.');
    }

    public function update(Request $request, Employee $employee)
    {
        abort_unless(auth()->user()->hasPermission('edit_employees'), 403);

        $validated = $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'email'         => ['required', 'email', 'unique:users,email,' . $employee->user_id],
            'status'        => ['required', 'string', 'in:active,inactive,suspended'],
            'profile_image' => ['nullable', 'image', 'max:2048'],
            'department_id' => ['required', 'exists:departments,id'],
            'manager_id'    => ['nullable', 'exists:employees,id'],
            'position'      => ['required', 'string', 'max:255'],
            'hire_date'     => ['required', 'date', 'before_or_equal:today'],
            'type'          => ['required', Rule::enum(EmployeeType::class)],
            'salary'        => [
                'nullable', 'numeric', 'min:0',
                Rule::requiredIf(fn () => $request->input('type') === EmployeeType::ContractEmployee->value),
            ],
            'hourly_rate'   => [
                'nullable', 'numeric', 'min:0',
                Rule::requiredIf(fn () => $request->input('type') === EmployeeType::HourlyEmployee->value),
            ],
        ]);

        $userUpdate = [
            'name'   => $validated['name'],
            'email'  => $validated['email'],
            'status' => $validated['status'],
        ];

        if ($request->hasFile('profile_image')) {
            if ($employee->user->profile_image) {
                Storage::disk('public')->delete($employee->user->profile_image);
            }
            $userUpdate['profile_image'] = $request->file('profile_image')->store('profile-images', 'public');
        }

        $employee->user->update($userUpdate);

        if ($request->filled('role_id')) {
            $employee->user->roles()->sync([$request->role_id]);
        } else {
            $employee->user->roles()->detach();
        }

        $employee->update([
            'department_id' => $validated['department_id'],
            'manager_id'    => $validated['manager_id'] ?? null,
            'position'      => $validated['position'],
            'hire_date'     => $validated['hire_date'],
            'type'          => $validated['type'],
            'salary'        => $validated['salary'] ?? null,
            'hourly_rate'   => $validated['hourly_rate'] ?? null,
        ]);

        return redirect()->route('employees.show', $employee)
            ->with('success', 'Employee updated successfully.');
    }

    public function destroy(Employee $employee)
    {
        abort_unless(auth()->user()->hasPermission('delete_employees'), 403);

        if ($employee->user->profile_image) {
            Storage::disk('public')->delete($employee->user->profile_image);
        }

        $employee->delete();

        return redirect()->route('employees.index')
            ->with('success', 'Employee deleted successfully.');
    }

    public function storeAttachment(Request $request, Employee $employee)
    {
        abort_unless(auth()->user()->hasPermission('manage_employee_attachments'), 403);

        $request->validate([
            'key'  => ['required', 'string', 'max:255'],
            'file' => ['required', 'file', 'max:10240'],
        ]);

        $path = $request->file('file')->store(
            'employee-attachments/' . $employee->user_id,
            'public'
        );

        $employee->user->attachments()->create([
            'key'             => $request->key,
            'attachment_path' => $path,
        ]);

        return back()->with('success', 'Attachment uploaded successfully.');
    }

    public function destroyAttachment(Employee $employee, UserAttachment $userAttachment)
    {
        abort_unless(auth()->user()->hasPermission('manage_employee_attachments'), 403);
        abort_if($userAttachment->user_id !== $employee->user_id, 403);

        Storage::disk('public')->delete($userAttachment->attachment_path);
        $userAttachment->delete();

        return back()->with('success', 'Attachment deleted.');
    }

    public function sendJobOffer(Employee $employee)
    {
        abort_unless(auth()->user()->hasPermission('send_employee_documents'), 403);

        $employee->load(['user', 'department', 'manager.user']);

        abort_unless($employee->user->attachments()->where('key', 'job_offer')->exists(), 422, 'Job Offer document not found. Re-save the employee to regenerate documents.');

        Mail::to($employee->user->email)
            ->send(new EmployeeDocumentMail($employee, 'job_offer'));

        return back()->with('success', 'Job Offer sent to ' . $employee->user->email);
    }

    public function sendContract(Employee $employee)
    {
        abort_unless(auth()->user()->hasPermission('send_employee_documents'), 403);

        $employee->load(['user', 'department', 'manager.user']);

        abort_unless($employee->user->attachments()->where('key', 'contract')->exists(), 422, 'Contract document not found. Re-save the employee to regenerate documents.');

        Mail::to($employee->user->email)
            ->send(new EmployeeDocumentMail($employee, 'contract'));

        return back()->with('success', 'Contract sent to ' . $employee->user->email);
    }

    public function regenerateDocuments(Employee $employee)
    {
        abort_unless(auth()->user()->hasPermission('send_employee_documents'), 403);

        $employee->load(['user', 'department', 'manager.user']);

        app(DocumentService::class)->generateEmployeeDocuments($employee);

        return back()->with('success', 'Documents regenerated successfully.');
    }

    public function sendResetPassword(Employee $employee)
    {
        abort_unless(auth()->user()->hasPermission('edit_employees'), 403);

        Password::broker()->sendResetLink(['email' => $employee->user->email]);

        return back()->with('success', 'Password reset link sent to ' . $employee->user->email);
    }
}

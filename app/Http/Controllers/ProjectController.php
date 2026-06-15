<?php

namespace App\Http\Controllers;

use App\Enums\ProjectMemberRole;
use App\Enums\ProjectPriority;
use App\Enums\ProjectStatus;
use App\Models\Client;
use App\Models\Project;
use App\Models\ProjectAttachment;
use App\Models\ProjectMember;
use App\Models\ProjectStatusLog;
use App\Models\User;
use App\Notifications\ProjectAssignedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('view_projects'), 403);
        $query = Project::with(['client', 'creator'])->withCount('members');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('client', fn ($q) => $q->where('company_name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        $projects = $query->latest()->paginate(15)->withQueryString();
        $clients  = Client::orderBy('company_name')->get();

        return view('projects.index', compact('projects', 'clients'));
    }

    public function show(Project $project)
    {
        abort_unless(auth()->user()->hasPermission('view_projects'), 403);
        $project->load(['client', 'creator', 'members.user', 'attachments.uploader', 'statusLogs.changedBy']);
        $users   = User::orderBy('name')->get();
        $clients = Client::orderBy('company_name')->get();

        return view('projects.show', compact('project', 'users', 'clients'));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('create_projects'), 403);
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'client_id'   => ['required', 'exists:clients,id'],
            'status'      => ['required', Rule::enum(ProjectStatus::class)],
            'priority'    => ['required', Rule::enum(ProjectPriority::class)],
            'start_date'  => ['nullable', 'date'],
            'due_date'    => ['nullable', 'date'],
            'budget'      => ['nullable', 'numeric', 'min:0'],
        ]);

        $project = Project::create([
            ...$validated,
            'created_by' => auth()->id(),
        ]);

        ProjectStatusLog::create([
            'project_id' => $project->id,
            'log_key'    => 'Project created',
            'changed_by' => auth()->id(),
            'log_at'     => now(),
        ]);

        return redirect()->route('projects.show', $project)
            ->with('success', 'Project created successfully.');
    }

    public function update(Request $request, Project $project)
    {
        abort_unless(auth()->user()->hasPermission('edit_projects'), 403);
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'client_id'   => ['required', 'exists:clients,id'],
            'status'      => ['required', Rule::enum(ProjectStatus::class)],
            'priority'    => ['required', Rule::enum(ProjectPriority::class)],
            'start_date'  => ['nullable', 'date'],
            'due_date'    => ['nullable', 'date'],
            'budget'      => ['nullable', 'numeric', 'min:0'],
        ]);

        $oldStatus = $project->status->value;

        if ($oldStatus !== $validated['status']) {
            if ($validated['status'] === 'completed') {
                $validated['completed_at'] = now();
            } elseif ($oldStatus === 'completed') {
                $validated['completed_at'] = null;
            }

            ProjectStatusLog::create([
                'project_id' => $project->id,
                'log_key'    => 'Status changed to ' . $validated['status'],
                'changed_by' => auth()->id(),
                'log_at'     => now(),
            ]);
        }

        $project->update($validated);

        return redirect()->route('projects.show', $project)
            ->with('success', 'Project updated successfully.');
    }

    public function destroy(Project $project)
    {
        abort_unless(auth()->user()->hasPermission('delete_projects'), 403);
        $project->delete();

        return redirect()->route('projects.index')
            ->with('success', 'Project deleted successfully.');
    }

    public function storeMember(Request $request, Project $project)
    {
        abort_unless(auth()->user()->hasPermission('edit_projects'), 403);
        $request->validate([
            'user_id'         => ['required', 'exists:users,id'],
            'role_in_project' => ['required', Rule::enum(ProjectMemberRole::class)],
        ]);

        $member = ProjectMember::firstOrCreate(
            ['project_id' => $project->id, 'user_id' => $request->user_id],
            ['role_in_project' => $request->role_in_project, 'joined_at' => now()]
        );

        if ($member->wasRecentlyCreated && $request->user_id != auth()->id()) {
            User::find($request->user_id)?->notify(new ProjectAssignedNotification($project, auth()->user()));
        }

        return back()->with('success', 'Member added to project.');
    }

    public function destroyMember(Project $project, ProjectMember $member)
    {
        abort_unless(auth()->user()->hasPermission('edit_projects'), 403);
        abort_if($member->project_id !== $project->id, 403);
        $member->delete();

        return back()->with('success', 'Member removed from project.');
    }

    public function storeAttachment(Request $request, Project $project)
    {
        abort_unless(auth()->user()->hasPermission('edit_projects'), 403);
        $request->validate([
            'file_name' => ['required', 'string', 'max:255'],
            'file'      => ['required', 'file', 'max:10240'],
        ]);

        $path = $request->file('file')->store('project-attachments/' . $project->id, 'public');

        $project->attachments()->create([
            'file_name'   => $request->file_name,
            'file_path'   => $path,
            'uploaded_by' => auth()->id(),
        ]);

        return back()->with('success', 'Attachment uploaded.');
    }

    public function destroyAttachment(Project $project, ProjectAttachment $attachment)
    {
        abort_unless(auth()->user()->hasPermission('edit_projects'), 403);
        abort_if($attachment->project_id !== $project->id, 403);
        Storage::disk('public')->delete($attachment->file_path);
        $attachment->delete();

        return back()->with('success', 'Attachment deleted.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskAssignee;
use App\Models\User;
use App\Notifications\TaskAssignedNotification;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('view_tasks'), 403);
        $viewingAll = auth()->user()->hasPermission('view_all_tasks');
        $tab        = ($viewingAll && $request->get('tab') === 'all') ? 'all' : 'mine';

        $query = Task::with(['project', 'createdBy', 'assignees'])->withCount('assignees');

        if ($tab === 'mine') {
            $userId = auth()->id();
            $query->where(function ($q) use ($userId) {
                $q->where('created_by', $userId)
                  ->orWhereHas('assignees', fn ($a) => $a->where('user_id', $userId));
            });
        }

        if ($request->filled('search')) {
            $query->where('title', 'like', "%{$request->search}%");
        }
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }
        if ($tab === 'all') {
            if ($request->filled('assigned_to')) {
                $query->whereHas('assignees', fn ($q) => $q->where('user_id', $request->assigned_to));
            }
            if ($request->filled('created_by_user')) {
                $query->where('created_by', $request->created_by_user);
            }
        }

        $tasks      = $query->latest()->paginate(20)->withQueryString();
        $projects   = Project::orderBy('name')->get();
        $users      = User::orderBy('name')->get();
        $statuses   = TaskStatus::cases();
        $priorities = TaskPriority::cases();

        return view('tasks.index', compact('tasks', 'projects', 'users', 'statuses', 'priorities', 'viewingAll', 'tab'));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('create_tasks'), 403);
        $data = $request->validate([
            'project_id'      => ['required', 'exists:projects,id'],
            'title'           => ['required', 'string', 'max:255'],
            'description'     => ['nullable', 'string'],
            'status'          => ['required', Rule::enum(TaskStatus::class)],
            'priority'        => ['required', Rule::enum(TaskPriority::class)],
            'start_date'      => ['nullable', 'date'],
            'due_date'        => ['nullable', 'date'],
            'estimated_hours' => ['nullable', 'numeric', 'min:0'],
            'assignees'       => ['nullable', 'array'],
            'assignees.*'     => ['exists:users,id'],
        ]);

        $data['created_by'] = auth()->id();
        $data['updated_by'] = auth()->id();

        $assignees = $data['assignees'] ?? [];
        unset($data['assignees']);

        $task = Task::create($data);

        $actor = auth()->user();
        foreach ($assignees as $userId) {
            TaskAssignee::create([
                'task_id'     => $task->id,
                'user_id'     => $userId,
                'role'        => 'assignee',
                'assigned_at' => now(),
            ]);
            if ($userId != $actor->id) {
                User::find($userId)?->notify(new TaskAssignedNotification($task->load('project'), $actor));
            }
        }

        return back()->with('success', 'Task created.');
    }

    public function update(Request $request, Task $task)
    {
        abort_unless(auth()->user()->hasPermission('edit_tasks'), 403);
        $data = $request->validate([
            'project_id'      => ['required', 'exists:projects,id'],
            'title'           => ['required', 'string', 'max:255'],
            'description'     => ['nullable', 'string'],
            'status'          => ['required', Rule::enum(TaskStatus::class)],
            'priority'        => ['required', Rule::enum(TaskPriority::class)],
            'start_date'      => ['nullable', 'date'],
            'due_date'        => ['nullable', 'date'],
            'estimated_hours' => ['nullable', 'numeric', 'min:0'],
            'assignees'       => ['nullable', 'array'],
            'assignees.*'     => ['exists:users,id'],
        ]);

        $data['updated_by'] = auth()->id();

        if ($data['status'] === TaskStatus::Done->value && ! $task->completed_at) {
            $data['completed_at'] = now();
        } elseif ($data['status'] !== TaskStatus::Done->value) {
            $data['completed_at'] = null;
        }

        $assignees = $data['assignees'] ?? [];
        unset($data['assignees']);

        $task->update($data);

        $previousAssignees = $task->assignees()->pluck('user_id')->toArray();
        $task->assignees()->delete();

        $actor = auth()->user();
        foreach ($assignees as $userId) {
            TaskAssignee::create([
                'task_id'     => $task->id,
                'user_id'     => $userId,
                'role'        => 'assignee',
                'assigned_at' => now(),
            ]);
            if ($userId != $actor->id && ! in_array($userId, $previousAssignees)) {
                User::find($userId)?->notify(new TaskAssignedNotification($task->load('project'), $actor));
            }
        }

        return back()->with('success', 'Task updated.');
    }

    public function destroy(Task $task)
    {
        abort_unless(auth()->user()->hasPermission('delete_tasks'), 403);
        $task->delete();

        return back()->with('success', 'Task deleted.');
    }
}

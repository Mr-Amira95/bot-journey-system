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
        $viewingAll   = auth()->user()->hasPermission('view_all_tasks');
        $tab          = ($viewingAll && $request->get('tab') === 'all') ? 'all' : 'mine';
        $canEditTasks = auth()->user()->hasPermission('edit_tasks');

        $query = Task::with(['project', 'createdBy', 'assignees.user']);

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

        $projects   = Project::orderBy('name')->get();
        $users      = User::orderBy('name')->get();
        $statuses   = TaskStatus::cases();
        $priorities = TaskPriority::cases();

        $groupBy = in_array($request->get('group_by'), ['project', 'status'], true) ? $request->get('group_by') : null;

        $tasks  = null;
        $groups = null;

        if ($groupBy) {
            $allTasks = (clone $query)->latest()->get();

            if ($groupBy === 'project') {
                $groups = $allTasks->groupBy(fn ($t) => $t->project->name)->sortKeys();
            } else {
                $byStatus = $allTasks->groupBy(fn ($t) => $t->status->value);
                $groups   = collect($statuses)
                    ->mapWithKeys(fn ($s) => [$s->label() => $byStatus->get($s->value, collect())])
                    ->filter(fn ($c) => $c->isNotEmpty());
            }
        } else {
            $tasks = $query->latest()->paginate(20)->withQueryString();
        }

        $editTask = null;
        if ($canEditTasks && $request->filled('edit')) {
            $editTask = Task::with('assignees')->find($request->get('edit'));
            if ($editTask && $tab === 'mine') {
                $userId = auth()->id();
                $owns   = $editTask->created_by === $userId || $editTask->assignees()->where('user_id', $userId)->exists();
                if (! $owns) {
                    $editTask = null;
                }
            }
        }

        return view('tasks.index', compact(
            'tasks', 'groups', 'groupBy', 'projects', 'users', 'statuses', 'priorities', 'viewingAll', 'tab', 'editTask'
        ));
    }

    public function show(Task $task)
    {
        abort_unless(auth()->user()->hasPermission('view_tasks'), 403);
        $viewingAll = auth()->user()->hasPermission('view_all_tasks');

        if (! $viewingAll) {
            $userId = auth()->id();
            $owns   = $task->created_by === $userId || $task->assignees()->where('user_id', $userId)->exists();
            abort_unless($owns, 403);
        }

        $task->load(['project', 'createdBy', 'updatedBy', 'assignees.user']);

        $canEditTasks   = auth()->user()->hasPermission('edit_tasks');
        $canDeleteTasks = auth()->user()->hasPermission('delete_tasks');

        return view('tasks.show', compact('task', 'canEditTasks', 'canDeleteTasks', 'viewingAll'));
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

    public function quickUpdate(Request $request, Task $task)
    {
        abort_unless(auth()->user()->hasPermission('edit_tasks'), 403);

        $data = $request->validate([
            'status'      => ['sometimes', Rule::enum(TaskStatus::class)],
            'priority'    => ['sometimes', Rule::enum(TaskPriority::class)],
            'due_date'    => ['sometimes', 'nullable', 'date'],
            'assignees'   => ['sometimes', 'array'],
            'assignees.*' => ['exists:users,id'],
        ]);

        if (array_key_exists('status', $data) || array_key_exists('priority', $data) || array_key_exists('due_date', $data)) {
            if (array_key_exists('status', $data)) {
                $task->status = $data['status'];
                if ($data['status'] === TaskStatus::Done->value && ! $task->completed_at) {
                    $task->completed_at = now();
                } elseif ($data['status'] !== TaskStatus::Done->value) {
                    $task->completed_at = null;
                }
            }
            if (array_key_exists('priority', $data)) {
                $task->priority = $data['priority'];
            }
            if (array_key_exists('due_date', $data)) {
                $task->due_date = $data['due_date'];
            }
            $task->updated_by = auth()->id();
            $task->save();
        }

        if (array_key_exists('assignees', $data)) {
            $previousAssignees = $task->assignees()->pluck('user_id')->toArray();
            $task->assignees()->delete();

            $actor = auth()->user();
            foreach ($data['assignees'] as $userId) {
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
        }

        $task->refresh()->load('assignees.user');

        return response()->json([
            'status'           => $task->status->value,
            'status_label'     => $task->status->label(),
            'priority'         => $task->priority->value,
            'priority_label'   => ucfirst($task->priority->value),
            'due_date'         => $task->due_date?->format('Y-m-d'),
            'due_date_display' => $task->due_date?->format('d M Y') ?? '—',
            'overdue'          => (bool) ($task->due_date && $task->due_date->isPast() && $task->status->value !== 'done'),
            'assignees'        => $task->assignees->map(fn ($a) => ['id' => $a->user_id, 'name' => $a->user->name])->values(),
        ]);
    }

    public function destroy(Task $task)
    {
        abort_unless(auth()->user()->hasPermission('delete_tasks'), 403);
        $task->delete();

        return back()->with('success', 'Task deleted.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Enums\TaskLogAction;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskAssignee;
use App\Models\TaskAttachment;
use App\Models\TaskComment;
use App\Models\User;
use App\Notifications\TaskAssignedNotification;
use App\Notifications\TaskCommentNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class TaskController extends Controller
{
    private const ATTACHMENT_MIMES = 'jpg,jpeg,png,gif,webp,mp4,mov,avi,webm,pdf,doc,docx,xls,xlsx,ppt,pptx,txt,zip';

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

        $task->load([
            'project', 'createdBy', 'updatedBy', 'assignees.user',
            'comments' => fn ($q) => $q->whereNull('parent_id')
                ->with(['user', 'attachments.user', 'replies.user', 'replies.attachments.user'])
                ->oldest(),
            'attachments.user',
        ]);

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
            'attachments'     => ['nullable', 'array'],
            'attachments.*'   => ['file', 'max:20480', 'mimes:' . self::ATTACHMENT_MIMES],
        ]);

        $data['created_by'] = auth()->id();
        $data['updated_by'] = auth()->id();

        $assignees   = $data['assignees'] ?? [];
        $attachments = $data['attachments'] ?? [];
        unset($data['assignees'], $data['attachments']);

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

        foreach ($attachments as $file) {
            $this->createAttachment($task, $file);
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
            'attachments'     => ['nullable', 'array'],
            'attachments.*'   => ['file', 'max:20480', 'mimes:' . self::ATTACHMENT_MIMES],
        ]);

        $data['updated_by'] = auth()->id();

        if ($data['status'] === TaskStatus::Done->value && ! $task->completed_at) {
            $data['completed_at'] = now();
        } elseif ($data['status'] !== TaskStatus::Done->value) {
            $data['completed_at'] = null;
        }

        $assignees   = $data['assignees'] ?? [];
        $attachments = $data['attachments'] ?? [];
        unset($data['assignees'], $data['attachments']);

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

        foreach ($attachments as $file) {
            $this->createAttachment($task, $file);
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

    public function storeComment(Request $request, Task $task)
    {
        abort_unless(auth()->user()->hasPermission('edit_tasks'), 403);

        $data = $request->validate([
            'comment'       => ['required', 'string', 'max:5000'],
            'parent_id'     => ['nullable', 'exists:task_comments,id'],
            'attachments'   => ['nullable', 'array'],
            'attachments.*' => ['file', 'max:20480', 'mimes:' . self::ATTACHMENT_MIMES],
        ]);

        if (! empty($data['parent_id'])) {
            abort_unless(TaskComment::where('id', $data['parent_id'])->where('task_id', $task->id)->exists(), 403);
        }

        $comment = $task->comments()->create([
            'user_id'   => auth()->id(),
            'comment'   => $data['comment'],
            'parent_id' => $data['parent_id'] ?? null,
        ]);

        foreach ($data['attachments'] ?? [] as $file) {
            $this->createAttachment($task, $file, $comment);
        }

        $task->logs()->create([
            'user_id'     => auth()->id(),
            'action'      => TaskLogAction::CommentAdded,
            'description' => 'Added a comment.',
        ]);

        $actor = auth()->user();
        $task->load('assignees.user');
        $recipients = $task->assignees->pluck('user')->filter(fn ($user) => $user && $user->id !== $actor->id)->unique('id');
        foreach ($recipients as $recipient) {
            $recipient->notify(new TaskCommentNotification($task, $comment, $actor));
        }

        return back()->with('success', 'Comment added.');
    }

    public function destroyComment(Task $task, TaskComment $comment)
    {
        abort_unless(auth()->user()->hasPermission('edit_tasks'), 403);
        abort_if($comment->task_id !== $task->id, 403);

        $comment->delete();

        return back()->with('success', 'Comment deleted.');
    }

    public function storeAttachment(Request $request, Task $task)
    {
        abort_unless(auth()->user()->hasPermission('edit_tasks'), 403);

        $request->validate([
            'file' => ['required', 'file', 'max:20480', 'mimes:' . self::ATTACHMENT_MIMES],
        ]);

        $this->createAttachment($task, $request->file('file'));

        return back()->with('success', 'Attachment uploaded.');
    }

    public function destroyAttachment(Task $task, TaskAttachment $attachment)
    {
        abort_unless(auth()->user()->hasPermission('edit_tasks'), 403);
        abort_if($attachment->task_id !== $task->id, 403);

        Storage::disk('public')->delete($attachment->file_path);
        $attachment->delete();

        return back()->with('success', 'Attachment deleted.');
    }

    private function createAttachment(Task $task, $file, ?TaskComment $comment = null): TaskAttachment
    {
        $folder = 'task-attachments/' . $task->id . ($comment ? '/comments/' . $comment->id : '');
        $path   = $file->store($folder, 'public');

        $attachment = TaskAttachment::create([
            'task_id'    => $task->id,
            'comment_id' => $comment?->id,
            'user_id'    => auth()->id(),
            'file_name'  => $file->getClientOriginalName(),
            'file_path'  => $path,
            'file_type'  => $this->attachmentType($file->getClientOriginalExtension()),
            'file_size'  => $file->getSize(),
        ]);

        if (! $comment) {
            $task->logs()->create([
                'user_id'     => auth()->id(),
                'action'      => TaskLogAction::AttachmentAdded,
                'description' => 'Added attachment "' . $file->getClientOriginalName() . '".',
            ]);
        }

        return $attachment;
    }

    private function attachmentType(string $extension): string
    {
        $extension = strtolower($extension);

        return match (true) {
            in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true) => 'photo',
            in_array($extension, ['mp4', 'mov', 'avi', 'webm'], true)        => 'video',
            $extension === 'pdf'                                             => 'pdf',
            in_array($extension, ['doc', 'docx'], true)                      => 'word',
            in_array($extension, ['xls', 'xlsx'], true)                      => 'excel',
            in_array($extension, ['ppt', 'pptx'], true)                      => 'powerpoint',
            default                                                          => 'file',
        };
    }
}

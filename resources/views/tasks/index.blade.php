@extends('layouts.app')

@section('title', 'Tasks')
@section('page-title', 'Tasks')

@section('header-actions')
    @if(auth()->user()->hasPermission('create_tasks'))
    <button @click="$dispatch('panel:create')"
            class="inline-flex items-center gap-2 px-4 py-2 bg-[#E26B3D] text-white text-sm font-medium rounded-lg hover:bg-[#c85a2f] transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        New Task
    </button>
    @endif
@endsection

@section('content')
@if($viewingAll)
<div class="flex items-center gap-1 bg-slate-100 rounded-xl p-1 w-fit mb-5">
    <a href="{{ route('tasks.index', ['tab' => 'mine']) }}"
       class="px-4 py-2 rounded-lg text-sm font-medium transition-all {{ $tab === 'mine' ? 'bg-white shadow text-slate-800' : 'text-slate-500 hover:text-slate-700' }}">
        My Tasks
    </a>
    <a href="{{ route('tasks.index', ['tab' => 'all']) }}"
       class="px-4 py-2 rounded-lg text-sm font-medium transition-all flex items-center gap-1.5 {{ $tab === 'all' ? 'bg-white shadow text-slate-800' : 'text-slate-500 hover:text-slate-700' }}">
        <svg class="w-3.5 h-3.5 {{ $tab === 'all' ? 'text-[#E26B3D]' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
        </svg>
        All Tasks
    </a>
</div>
@endif
<div x-data="{
        panelOpen: {{ $errors->any() ? 'true' : 'false' }},
        _mode: '{{ old('_mode', 'create') }}',
        record_id: {{ old('record_id', 'null') }},
        formData: {
            project_id:      '{{ old('project_id', '') }}',
            title:           '{{ old('title', '') }}',
            description:     '{{ old('description', '') }}',
            status:          '{{ old('status', 'todo') }}',
            priority:        '{{ old('priority', 'medium') }}',
            start_date:      '{{ old('start_date', '') }}',
            due_date:        '{{ old('due_date', '') }}',
            estimated_hours: '{{ old('estimated_hours', '') }}',
            assignees:       {!! json_encode(array_map('strval', old('assignees', []))) !!},
        },
        openCreate() {
            this._mode = 'create';
            this.record_id = null;
            this.formData = { project_id: '', title: '', description: '', status: 'todo', priority: 'medium', start_date: '', due_date: '', estimated_hours: '', assignees: [] };
            this.panelOpen = true;
        },
        openEdit(data) {
            this._mode = 'edit';
            this.record_id = data.id;
            this.formData = { ...data };
            this.panelOpen = true;
        },
        close() { this.panelOpen = false; }
    }"
     @panel:create.window="openCreate()">

    {{-- Filters --}}
    <form method="GET" action="{{ route('tasks.index') }}" class="mb-6 flex flex-wrap gap-3 items-end">
        <input type="hidden" name="tab" value="{{ $tab }}">
        <div class="flex-1 min-w-48">
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Search tasks…"
                   class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#E26B3D]/40 focus:border-[#E26B3D]">
        </div>
        <div class="min-w-44">
            <select name="project_id" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#E26B3D]/40 focus:border-[#E26B3D]">
                <option value="">All Projects</option>
                @foreach($projects as $project)
                    <option value="{{ $project->id }}" @selected(request('project_id') == $project->id)>{{ $project->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="min-w-36">
            <select name="status" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#E26B3D]/40 focus:border-[#E26B3D]">
                <option value="">All Statuses</option>
                @foreach($statuses as $s)
                    <option value="{{ $s->value }}" @selected(request('status') === $s->value)>{{ $s->label() }}</option>
                @endforeach
            </select>
        </div>
        <div class="min-w-36">
            <select name="priority" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#E26B3D]/40 focus:border-[#E26B3D]">
                <option value="">All Priorities</option>
                @foreach($priorities as $p)
                    <option value="{{ $p->value }}" @selected(request('priority') === $p->value)>{{ ucfirst($p->value) }}</option>
                @endforeach
            </select>
        </div>
        @if($tab === 'all')
        <div class="min-w-40">
            <select name="assigned_to" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#E26B3D]/40 focus:border-[#E26B3D]">
                <option value="">Any Assignee</option>
                @foreach($users as $u)
                    <option value="{{ $u->id }}" @selected(request('assigned_to') == $u->id)>{{ $u->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="min-w-40">
            <select name="created_by_user" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#E26B3D]/40 focus:border-[#E26B3D]">
                <option value="">Any Creator</option>
                @foreach($users as $u)
                    <option value="{{ $u->id }}" @selected(request('created_by_user') == $u->id)>{{ $u->name }}</option>
                @endforeach
            </select>
        </div>
        @endif
        <button type="submit"
                class="px-4 py-2 bg-[#E26B3D] text-white text-sm rounded-lg hover:bg-[#c85a2f] transition-colors">
            Filter
        </button>
        @if(request()->hasAny(['search', 'project_id', 'status', 'priority', 'assigned_to', 'created_by_user']))
            <a href="{{ route('tasks.index', ['tab' => $tab]) }}"
               class="px-4 py-2 text-sm text-slate-500 hover:text-slate-700 border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors">
                Clear
            </a>
        @endif
    </form>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-slate-600">Title</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-600">Project</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-600">Status</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-600">Priority</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-600">Due Date</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-600">Assignees</th>
                    <th class="px-4 py-3 text-right font-medium text-slate-600">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @php
                    $canEditTasks   = auth()->user()->hasPermission('edit_tasks');
                    $canDeleteTasks = auth()->user()->hasPermission('delete_tasks');
                @endphp
                @forelse($tasks as $task)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-4 py-3">
                            <p class="font-medium text-slate-800">{{ $task->title }}</p>
                            @if($task->description)
                                <p class="text-xs text-slate-400 truncate max-w-xs mt-0.5">{{ Str::limit($task->description, 60) }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-slate-600">
                            <a href="{{ route('projects.show', $task->project) }}" class="hover:text-[#E26B3D] transition-colors">
                                {{ $task->project->name }}
                            </a>
                        </td>
                        <td class="px-4 py-3">
                            @php
                                $statusColors = [
                                    'todo'        => 'bg-slate-100 text-slate-700',
                                    'in_progress' => 'bg-blue-100 text-blue-700',
                                    'review'      => 'bg-amber-100 text-amber-700',
                                    'done'        => 'bg-emerald-100 text-emerald-700',
                                    'blocked'     => 'bg-red-100 text-red-700',
                                ];
                                $sc = $statusColors[$task->status->value] ?? 'bg-slate-100 text-slate-700';
                            @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $sc }}">
                                {{ $task->status->label() }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            @php
                                $priorityColors = [
                                    'low'    => 'bg-slate-100 text-slate-600',
                                    'medium' => 'bg-blue-100 text-blue-700',
                                    'high'   => 'bg-orange-100 text-orange-700',
                                    'urgent' => 'bg-red-100 text-red-700',
                                ];
                                $pc = $priorityColors[$task->priority->value] ?? 'bg-slate-100 text-slate-600';
                            @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $pc }}">
                                {{ ucfirst($task->priority->value) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-slate-600 font-mono text-xs">
                            @if($task->due_date)
                                <span class="{{ $task->due_date->isPast() && $task->status->value !== 'done' ? 'text-red-600 font-semibold' : '' }}">
                                    {{ $task->due_date->format('d M Y') }}
                                </span>
                            @else
                                <span class="text-slate-400">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-slate-600">
                            @if($task->assignees_count > 0)
                                <span class="inline-flex items-center gap-1 text-xs text-slate-500">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    {{ $task->assignees_count }}
                                </span>
                            @else
                                <span class="text-slate-400 text-xs">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                @if($canEditTasks)
                                <button @click="openEdit({
                                            id:              {{ $task->id }},
                                            project_id:      '{{ $task->project_id }}',
                                            title:           {{ json_encode($task->title) }},
                                            description:     {{ json_encode($task->description ?? '') }},
                                            status:          '{{ $task->status->value }}',
                                            priority:        '{{ $task->priority->value }}',
                                            start_date:      '{{ $task->start_date?->format('Y-m-d') ?? '' }}',
                                            due_date:        '{{ $task->due_date?->format('Y-m-d') ?? '' }}',
                                            estimated_hours: '{{ $task->estimated_hours ?? '' }}',
                                            assignees:       {{ $task->assignees->pluck('user_id')->map(fn($id) => (string)$id)->toJson() }},
                                        })"
                                        class="text-xs text-slate-500 hover:text-[#E26B3D] transition-colors px-2 py-1 rounded hover:bg-orange-50">
                                    Edit
                                </button>
                                @endif
                                @if($canDeleteTasks)
                                <button @click="$dispatch('confirm:delete', { action: '{{ route('tasks.destroy', $task) }}' })"
                                        class="text-xs text-slate-500 hover:text-red-600 transition-colors px-2 py-1 rounded hover:bg-red-50">
                                    Delete
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center text-slate-400">
                            No tasks found.
                            @if(auth()->user()->hasPermission('create_tasks'))
                            <button @click="openCreate()" class="ml-1 text-[#E26B3D] hover:underline">Create the first one.</button>
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>

    @if($tasks->hasPages())
        <div class="mt-4">{{ $tasks->links() }}</div>
    @endif

    {{-- Slide-over backdrop --}}
    <div x-show="panelOpen"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="close()"
         class="fixed inset-0 bg-black/50 z-40"></div>

    {{-- Slide-over panel --}}
    <div x-show="panelOpen"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="translate-x-full"
         x-transition:enter-end="translate-x-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="translate-x-0"
         x-transition:leave-end="translate-x-full"
         @panel:create.window="openCreate()"
         class="fixed top-0 right-0 h-full w-full max-w-lg bg-white shadow-2xl z-50 flex flex-col overflow-hidden">

        {{-- Panel header --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 shrink-0">
            <h2 class="text-base font-semibold text-slate-800"
                x-text="_mode === 'create' ? 'New Task' : 'Edit Task'"></h2>
            <button @click="close()" class="p-1.5 rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Form --}}
        <form :action="_mode === 'create' ? '{{ route('tasks.store') }}' : '{{ url('tasks') }}/' + record_id"
              method="POST"
              class="flex-1 overflow-y-auto">
            @csrf
            <input type="hidden" name="_method" :value="_mode === 'create' ? 'POST' : 'POST'">
            <input type="hidden" name="record_id" :value="record_id">
            <input type="hidden" name="_mode" :value="_mode">

            @if($errors->any())
                <div class="mx-6 mt-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                    <ul class="text-sm text-red-600 space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="px-6 py-4 space-y-5">

                {{-- Task Details --}}
                <fieldset>
                    <legend class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-3">Task Details</legend>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Title <span class="text-red-500">*</span></label>
                            <input type="text" name="title"
                                   x-effect="$el.value = formData.title"
                                   @input="formData.title = $event.target.value"
                                   class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#E26B3D]/40 focus:border-[#E26B3D]"
                                   placeholder="Task title…" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Description</label>
                            <textarea name="description" rows="3" @input="formData.description = $event.target.value"
                                      class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#E26B3D]/40 focus:border-[#E26B3D] resize-none"
                                      placeholder="Optional description…"
                                      x-effect="$el.value = formData.description"></textarea>
                        </div>
                    </div>
                </fieldset>

                {{-- Context --}}
                <fieldset>
                    <legend class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-3">Context</legend>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Project <span class="text-red-500">*</span></label>
                            <select name="project_id" @change="formData.project_id = $event.target.value"
                                    class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#E26B3D]/40 focus:border-[#E26B3D]"
                                    :class="panelOpen && !formData.project_id ? 'border-red-300' : ''"
                                    x-effect="$el.value = formData.project_id" required>
                                <option value="">Select project…</option>
                                @foreach($projects as $project)
                                    <option value="{{ $project->id }}">{{ $project->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Status <span class="text-red-500">*</span></label>
                                <select name="status" @change="formData.status = $event.target.value"
                                        class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#E26B3D]/40 focus:border-[#E26B3D]"
                                        x-effect="$el.value = formData.status" required>
                                    @foreach($statuses as $s)
                                        <option value="{{ $s->value }}">{{ $s->label() }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Priority <span class="text-red-500">*</span></label>
                                <select name="priority" @change="formData.priority = $event.target.value"
                                        class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#E26B3D]/40 focus:border-[#E26B3D]"
                                        x-effect="$el.value = formData.priority" required>
                                    @foreach($priorities as $p)
                                        <option value="{{ $p->value }}">{{ ucfirst($p->value) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </fieldset>

                {{-- Assignees --}}
                <fieldset>
                    <legend class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-3">Assignees</legend>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Assign To</label>
                        <select name="assignees[]" multiple
                                x-effect="Array.from($el.options).forEach(o => o.selected = formData.assignees.includes(o.value))"
                                @change="formData.assignees = Array.from($el.selectedOptions).map(o => o.value)"
                                class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#E26B3D]/40 focus:border-[#E26B3D]"
                                size="5">
                            @foreach($users as $u)
                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-slate-400">Hold Ctrl / Cmd to select multiple.</p>
                    </div>
                </fieldset>

                {{-- Schedule --}}
                <fieldset>
                    <legend class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-3">Schedule</legend>
                    <div class="space-y-3">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Start Date</label>
                                <input type="date" name="start_date" :value="formData.start_date" @change="formData.start_date = $event.target.value"
                                       class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#E26B3D]/40 focus:border-[#E26B3D]">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Due Date</label>
                                <input type="date" name="due_date" :value="formData.due_date" @change="formData.due_date = $event.target.value"
                                       class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#E26B3D]/40 focus:border-[#E26B3D]">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Estimated Hours</label>
                            <input type="number" name="estimated_hours" :value="formData.estimated_hours" @input="formData.estimated_hours = $event.target.value"
                                   step="0.5" min="0"
                                   class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#E26B3D]/40 focus:border-[#E26B3D]"
                                   placeholder="e.g. 4.5">
                        </div>
                    </div>
                </fieldset>

            </div>

            {{-- Panel footer --}}
            <div class="px-6 py-4 border-t border-slate-200 bg-slate-50 flex items-center justify-end gap-3 shrink-0">
                <button type="button" @click="close()"
                        class="px-4 py-2 text-sm text-slate-600 border border-slate-300 rounded-lg hover:bg-slate-100 transition-colors">
                    Cancel
                </button>
                <button type="submit"
                        class="px-4 py-2 text-sm text-white bg-[#E26B3D] rounded-lg hover:bg-[#c85a2f] transition-colors">
                    <span x-text="_mode === 'create' ? 'Create Task' : 'Save Changes'"></span>
                </button>
            </div>
        </form>
    </div>

</div>
@endsection

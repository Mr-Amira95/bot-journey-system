@extends('layouts.app')

@section('title', 'Projects')
@section('page-title', 'Projects')

@section('header-actions')
    @if(auth()->user()->hasPermission('create_projects'))
    <button @click="$dispatch('panel:create')"
            class="inline-flex items-center gap-2 rounded-lg bg-[#E26B3D] px-4 py-2 text-sm font-mono font-medium text-white hover:bg-[#c8602a] transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        New Project
    </button>
    @endif
@endsection

@section('content')
<div x-data="{
    open: {{ $errors->any() ? 'true' : 'false' }},
    mode: '{{ old('_mode', 'create') }}',
    recordId: {{ old('record_id', 'null') }},
    submitted: false,
    formData: {
        name:        '{{ old('name', '') }}',
        description: '{{ old('description', '') }}',
        client_id:   '{{ old('client_id', '') }}',
        status:      '{{ old('status', 'planning') }}',
        priority:    '{{ old('priority', 'medium') }}',
        start_date:  '{{ old('start_date', '') }}',
        due_date:    '{{ old('due_date', '') }}',
        budget:      '{{ old('budget', '') }}'
    },
    openCreate() {
        this.mode = 'create';
        this.recordId = null;
        this.submitted = false;
        this.formData = { name: '', description: '', client_id: '', status: 'planning', priority: 'medium', start_date: '', due_date: '', budget: '' };
        this.open = true;
    },
    openEdit(data) {
        this.mode = 'edit';
        this.recordId = data.id;
        this.submitted = false;
        this.formData = data;
        this.open = true;
    },
    close() { this.open = false; this.submitted = false; }
}" @panel:create.window="openCreate()">

    {{-- Filters --}}
    <div class="mb-5">
        <form method="GET" action="{{ route('projects.index') }}" class="flex flex-wrap items-center gap-3">
            <div class="relative flex-1 min-w-52 max-w-sm">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search by name or client..."
                       class="w-full pl-9 pr-4 py-2 rounded-lg border border-slate-300 bg-white text-sm text-slate-800 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-[#E26B3D] font-mono">
            </div>
            <select name="status"
                    class="rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#E26B3D] font-mono">
                <option value="">All Statuses</option>
                @foreach(App\Enums\ProjectStatus::cases() as $s)
                    <option value="{{ $s->value }}" {{ request('status') === $s->value ? 'selected' : '' }}>
                        {{ ucwords(str_replace('_', ' ', $s->value)) }}
                    </option>
                @endforeach
            </select>
            <select name="priority"
                    class="rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#E26B3D] font-mono">
                <option value="">All Priorities</option>
                @foreach(App\Enums\ProjectPriority::cases() as $p)
                    <option value="{{ $p->value }}" {{ request('priority') === $p->value ? 'selected' : '' }}>
                        {{ ucfirst($p->value) }}
                    </option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 rounded-lg bg-white border border-slate-300 text-sm text-slate-700 hover:bg-stone-50 transition-colors font-mono">Search</button>
            @if(request()->hasAny(['search', 'status', 'priority']))
                <a href="{{ route('projects.index') }}" class="text-sm text-slate-500 hover:text-slate-700 font-mono">Clear</a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-stone-50 border-b border-slate-200">
                <tr>
                    <th class="text-left px-5 py-3.5 text-xs font-mono font-medium text-slate-500 uppercase tracking-wider">Project</th>
                    <th class="text-left px-5 py-3.5 text-xs font-mono font-medium text-slate-500 uppercase tracking-wider">Client</th>
                    <th class="text-left px-5 py-3.5 text-xs font-mono font-medium text-slate-500 uppercase tracking-wider">Status</th>
                    <th class="text-left px-5 py-3.5 text-xs font-mono font-medium text-slate-500 uppercase tracking-wider">Priority</th>
                    <th class="text-left px-5 py-3.5 text-xs font-mono font-medium text-slate-500 uppercase tracking-wider">Due Date</th>
                    <th class="text-center px-5 py-3.5 text-xs font-mono font-medium text-slate-500 uppercase tracking-wider">Members</th>
                    <th class="text-right px-5 py-3.5 text-xs font-mono font-medium text-slate-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @php
                    $canEditProj   = auth()->user()->hasPermission('edit_projects');
                    $canDeleteProj = auth()->user()->hasPermission('delete_projects');
                @endphp
                @forelse($projects as $project)
                @php
                    $statusColor = match($project->status->value) {
                        'planning'  => 'bg-blue-100 text-blue-700',
                        'active'    => 'bg-green-100 text-green-700',
                        'on_hold'   => 'bg-amber-100 text-amber-700',
                        'completed' => 'bg-emerald-100 text-emerald-700',
                        default     => 'bg-slate-100 text-slate-600',
                    };
                    $priorityColor = match($project->priority->value) {
                        'low'    => 'bg-slate-100 text-slate-600',
                        'medium' => 'bg-blue-100 text-blue-700',
                        'high'   => 'bg-orange-100 text-orange-700',
                        'urgent' => 'bg-red-100 text-red-700',
                        default  => 'bg-slate-100 text-slate-600',
                    };
                @endphp
                <tr class="hover:bg-stone-50/60 transition-colors">
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-[#E26B3D] flex items-center justify-center text-[#F2EEE5] font-bold text-sm shrink-0">
                                {{ strtoupper(substr($project->name, 0, 1)) }}
                            </div>
                            <div>
                                <p class="font-medium text-slate-800">{{ $project->name }}</p>
                                @if($project->budget)
                                    <p class="text-xs font-mono text-slate-400">${{ number_format($project->budget, 0) }}</p>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-4 text-slate-600 font-mono text-xs">{{ $project->client?->company_name ?? '—' }}</td>
                    <td class="px-5 py-4">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-mono font-medium {{ $statusColor }}">
                            {{ ucwords(str_replace('_', ' ', $project->status->value)) }}
                        </span>
                    </td>
                    <td class="px-5 py-4">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-mono font-medium {{ $priorityColor }}">
                            {{ ucfirst($project->priority->value) }}
                        </span>
                    </td>
                    <td class="px-5 py-4 font-mono text-xs {{ $project->due_date && $project->due_date->isPast() && $project->status->value !== 'completed' ? 'text-red-600 font-semibold' : 'text-slate-600' }}">
                        {{ $project->due_date?->format('M j, Y') ?? '—' }}
                    </td>
                    <td class="px-5 py-4 text-center text-slate-600 font-mono text-xs">
                        {{ $project->members_count }}
                    </td>
                    <td class="px-5 py-4 text-right">
                        <div class="inline-flex items-center gap-1.5">
                            <a href="{{ route('projects.show', $project) }}"
                               class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors" title="View">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                            @if($canEditProj)
                            <button @click="openEdit({
                                        id:          {{ $project->id }},
                                        name:        '{{ e($project->name) }}',
                                        description: `{{ e($project->description ?? '') }}`,
                                        client_id:   '{{ $project->client_id }}',
                                        status:      '{{ $project->status->value }}',
                                        priority:    '{{ $project->priority->value }}',
                                        start_date:  '{{ $project->start_date?->format('Y-m-d') ?? '' }}',
                                        due_date:    '{{ $project->due_date?->format('Y-m-d') ?? '' }}',
                                        budget:      '{{ $project->budget ?? '' }}'
                                    })"
                                    class="p-1.5 rounded-lg text-slate-400 hover:text-[#E26B3D] hover:bg-[#E26B3D]/10 transition-colors" title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </button>
                            @endif
                            @if($canDeleteProj)
                            <button @click="$dispatch('confirm:delete', { action: '{{ route('projects.destroy', $project) }}' })"
                                    class="p-1.5 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-5 py-12 text-center text-slate-400">
                        <svg class="w-10 h-10 mx-auto mb-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                        </svg>
                        <p class="font-medium">No projects found</p>
                        <p class="text-sm mt-1 font-mono">Create your first project to get started.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>

    @if($projects->hasPages())
        <div class="mt-5">{{ $projects->links() }}</div>
    @endif

    {{-- Backdrop --}}
    <div x-show="open"
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         @click="close()"
         class="fixed inset-0 bg-[#0f1b3d]/50 backdrop-blur-sm z-40" style="display:none;"></div>

    {{-- Slide-over --}}
    <div x-show="open"
         x-transition:enter="transition ease-out duration-300" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
         x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
         class="fixed right-0 top-0 h-full w-full max-w-lg bg-white shadow-2xl z-50 flex flex-col" style="display:none;">

        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 shrink-0">
            <h2 class="text-base font-semibold text-slate-800" x-text="mode === 'create' ? 'New Project' : 'Edit Project'"></h2>
            <button @click="close()" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-stone-100 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto px-6 py-5">
            @if($errors->any())
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-3">
                @foreach($errors->all() as $error)
                    <p class="text-sm text-red-600 font-mono">{{ $error }}</p>
                @endforeach
            </div>
            @endif

            <form :action="mode === 'create' ? '{{ route('projects.store') }}' : '{{ url('projects') }}/' + recordId"
                  method="POST" class="space-y-5"
                  @submit="submitted = true">
                @csrf
                <input type="hidden" name="_mode" :value="mode">
                <input type="hidden" name="record_id" :value="recordId">

                {{-- Project Details --}}
                <div>
                    <p class="text-xs font-mono font-semibold text-[#E26B3D] uppercase tracking-widest mb-3">Project Details</p>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-mono font-medium text-slate-600 mb-1.5 uppercase tracking-wider">Project Name <span class="text-red-500">*</span></label>
                            <input type="text" name="name" x-model="formData.name"
                                   class="w-full rounded-lg border px-3.5 py-2.5 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#E26B3D]"
                                   :class="submitted && !(formData.name || '').trim() ? 'border-red-400' : 'border-slate-300'"
                                   placeholder="e.g. Website Redesign">
                        </div>
                        <div>
                            <label class="block text-xs font-mono font-medium text-slate-600 mb-1.5 uppercase tracking-wider">Description</label>
                            <textarea name="description" rows="3"
                                      x-effect="$el.value = formData.description"
                                      class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#E26B3D] resize-none font-mono"
                                      placeholder="Brief project overview..."></textarea>
                        </div>
                    </div>
                </div>

                <div class="border-t border-slate-100"></div>

                {{-- Client & Settings --}}
                <div>
                    <p class="text-xs font-mono font-semibold text-[#E26B3D] uppercase tracking-widest mb-3">Client & Settings</p>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-mono font-medium text-slate-600 mb-1.5 uppercase tracking-wider">Client <span class="text-red-500">*</span></label>
                            <select name="client_id"
                                    x-effect="$el.value = formData.client_id"
                                    @change="formData.client_id = $event.target.value"
                                    class="w-full rounded-lg border px-3.5 py-2.5 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#E26B3D] font-mono"
                                    :class="submitted && !formData.client_id ? 'border-red-400' : 'border-slate-300'">
                                <option value="">Select a client...</option>
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}">{{ $client->company_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-mono font-medium text-slate-600 mb-1.5 uppercase tracking-wider">Status</label>
                                <select name="status"
                                        x-effect="$el.value = formData.status"
                                        @change="formData.status = $event.target.value"
                                        class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#E26B3D] font-mono">
                                    @foreach(App\Enums\ProjectStatus::cases() as $s)
                                        <option value="{{ $s->value }}">{{ ucwords(str_replace('_', ' ', $s->value)) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-mono font-medium text-slate-600 mb-1.5 uppercase tracking-wider">Priority</label>
                                <select name="priority"
                                        x-effect="$el.value = formData.priority"
                                        @change="formData.priority = $event.target.value"
                                        class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#E26B3D] font-mono">
                                    @foreach(App\Enums\ProjectPriority::cases() as $p)
                                        <option value="{{ $p->value }}">{{ ucfirst($p->value) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-mono font-medium text-slate-600 mb-1.5 uppercase tracking-wider">Budget</label>
                            <div class="relative">
                                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm font-mono">$</span>
                                <input type="number" name="budget" :value="formData.budget" min="0" step="0.01"
                                       class="w-full rounded-lg border border-slate-300 pl-7 pr-3.5 py-2.5 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#E26B3D] font-mono"
                                       placeholder="0.00">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="border-t border-slate-100"></div>

                {{-- Timeline --}}
                <div>
                    <p class="text-xs font-mono font-semibold text-[#E26B3D] uppercase tracking-widest mb-3">Timeline</p>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-mono font-medium text-slate-600 mb-1.5 uppercase tracking-wider">Start Date</label>
                            <input type="date" name="start_date" :value="formData.start_date"
                                   class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#E26B3D] font-mono">
                        </div>
                        <div>
                            <label class="block text-xs font-mono font-medium text-slate-600 mb-1.5 uppercase tracking-wider">Due Date</label>
                            <input type="date" name="due_date" :value="formData.due_date"
                                   class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#E26B3D] font-mono">
                        </div>
                    </div>
                </div>

                <div class="pt-2 flex gap-3">
                    <button type="button" @click="close()"
                            class="flex-1 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-mono font-medium text-slate-700 hover:bg-stone-50 transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                            class="flex-1 rounded-lg bg-[#E26B3D] px-4 py-2.5 text-sm font-mono font-medium text-white hover:bg-[#c8602a] transition-colors">
                        <span x-text="mode === 'create' ? 'Create Project' : 'Save Changes'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

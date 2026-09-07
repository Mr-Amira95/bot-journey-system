@php
    $descriptionIsLong = $task->description && mb_strlen($task->description) > 60;
@endphp
<tr x-data="{
        quickUpdateUrl: '{{ route('tasks.quick-update', $task) }}',
        statusOptions: {{ $statusOptions->toJson() }},
        priorityOptions: {{ $priorityOptions->toJson() }},
        statusColors: { todo: 'bg-slate-100 text-slate-700', in_progress: 'bg-blue-100 text-blue-700', review: 'bg-amber-100 text-amber-700', done: 'bg-emerald-100 text-emerald-700', blocked: 'bg-red-100 text-red-700' },
        priorityColors: { low: 'bg-slate-100 text-slate-600', medium: 'bg-blue-100 text-blue-700', high: 'bg-orange-100 text-orange-700', urgent: 'bg-red-100 text-red-700' },
        status: '{{ $task->status->value }}',
        statusLabel: '{{ $task->status->label() }}',
        priority: '{{ $task->priority->value }}',
        priorityLabel: '{{ ucfirst($task->priority->value) }}',
        dueDate: '{{ $task->due_date?->format('Y-m-d') ?? '' }}',
        dueDateDisplay: '{{ $task->due_date?->format('d M Y') ?? '—' }}',
        overdue: {{ ($task->due_date && $task->due_date->isPast() && $task->status->value !== 'done') ? 'true' : 'false' }},
        assignees: {{ $task->assignees->map(fn ($a) => ['id' => $a->user_id, 'name' => $a->user->name])->values()->toJson() }},
        assigneeIds: {{ $task->assignees->pluck('user_id')->map(fn ($id) => (string) $id)->values()->toJson() }},
        statusOpen: false,
        priorityOpen: false,
        dueOpen: false,
        assigneesOpen: false,
        saving: false,
        statusMenuStyle: '',
        priorityMenuStyle: '',
        dueMenuStyle: '',
        assigneesMenuStyle: '',
        menuStyle(el) {
            const rect = el.getBoundingClientRect();
            return `top:${rect.bottom + 4}px; left:${rect.left}px;`;
        },
        closeMenus() {
            this.statusOpen = false;
            this.priorityOpen = false;
            this.dueOpen = false;
            this.assigneesOpen = false;
        },
        async quickUpdate(payload) {
            this.saving = true;
            try {
                const res = await fetch(this.quickUpdateUrl, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    },
                    body: JSON.stringify(payload),
                });
                if (!res.ok) throw new Error('Update failed');
                const data = await res.json();
                this.status = data.status;
                this.statusLabel = data.status_label;
                this.priority = data.priority;
                this.priorityLabel = data.priority_label;
                this.dueDate = data.due_date ?? '';
                this.dueDateDisplay = data.due_date_display;
                this.overdue = data.overdue;
                this.assignees = data.assignees;
                this.assigneeIds = data.assignees.map(a => String(a.id));
                return data;
            } finally {
                this.saving = false;
            }
        },
        async setStatus(value) {
            this.statusOpen = false;
            if (value === this.status) return;
            try { await this.quickUpdate({ status: value }); }
            catch (e) { alert('Could not update status.'); }
        },
        async setPriority(value) {
            this.priorityOpen = false;
            if (value === this.priority) return;
            try { await this.quickUpdate({ priority: value }); }
            catch (e) { alert('Could not update priority.'); }
        },
        async saveDueDate() {
            try { await this.quickUpdate({ due_date: this.dueDate || null }); this.dueOpen = false; }
            catch (e) { alert('Could not update due date.'); }
        },
        async toggleAssignee(id) {
            const idStr = String(id);
            const has = this.assigneeIds.includes(idStr);
            const next = has ? this.assigneeIds.filter(x => x !== idStr) : [...this.assigneeIds, idStr];
            try { await this.quickUpdate({ assignees: next }); }
            catch (e) { alert('Could not update assignees.'); }
        },
    }"
    @scroll.window.throttle="closeMenus()"
    @if($grouped ?? false) x-show="open" @endif
    class="hover:bg-slate-50 transition-colors">
    <td class="px-4 py-3">
        <a href="{{ route('tasks.show', $task) }}" class="font-medium text-slate-800 hover:text-[#E26B3D] transition-colors">
            {{ $task->title }}
        </a>
        @if($task->description)
            <p class="text-xs text-slate-400 max-w-xs mt-0.5">
                {{ Str::limit($task->description, 60) }}
                @if($descriptionIsLong)
                    <a href="{{ route('tasks.show', $task) }}" class="text-[#E26B3D] hover:underline whitespace-nowrap">More</a>
                @endif
            </p>
        @endif
    </td>
    <td class="px-4 py-3 text-slate-600">
        <a href="{{ route('projects.show', $task->project) }}" class="hover:text-[#E26B3D] transition-colors">
            {{ $task->project->name }}
        </a>
    </td>

    {{-- Status --}}
    <td class="px-4 py-3">
        @if($canEditTasks)
            <button type="button" x-ref="statusBtn"
                    @click="if (!statusOpen) statusMenuStyle = menuStyle($refs.statusBtn); statusOpen = !statusOpen; priorityOpen = false; dueOpen = false; assigneesOpen = false"
                    :disabled="saving"
                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium transition hover:ring-2 hover:ring-offset-1 hover:ring-slate-200 disabled:opacity-50"
                    :class="statusColors[status]">
                <span x-text="statusLabel"></span>
                <svg class="w-3 h-3 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <template x-teleport="body">
                <div x-show="statusOpen" x-transition @click.outside="statusOpen = false" :style="statusMenuStyle"
                     class="fixed z-[60] w-40 bg-white border border-slate-200 rounded-lg shadow-lg py-1">
                    <template x-for="opt in statusOptions" :key="opt.value">
                        <button type="button" @click="setStatus(opt.value)"
                                class="w-full text-left px-3 py-1.5 text-xs hover:bg-slate-50 flex items-center gap-2"
                                :class="opt.value === status ? 'font-semibold text-slate-800' : 'text-slate-600'">
                            <span class="w-2 h-2 rounded-full shrink-0" :class="statusColors[opt.value].split(' ')[0]"></span>
                            <span x-text="opt.label"></span>
                        </button>
                    </template>
                </div>
            </template>
        @else
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium" :class="statusColors[status]" x-text="statusLabel"></span>
        @endif
    </td>

    {{-- Priority --}}
    <td class="px-4 py-3">
        @if($canEditTasks)
            <button type="button" x-ref="priorityBtn"
                    @click="if (!priorityOpen) priorityMenuStyle = menuStyle($refs.priorityBtn); priorityOpen = !priorityOpen; statusOpen = false; dueOpen = false; assigneesOpen = false"
                    :disabled="saving"
                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium transition hover:ring-2 hover:ring-offset-1 hover:ring-slate-200 disabled:opacity-50"
                    :class="priorityColors[priority]">
                <span x-text="priorityLabel"></span>
                <svg class="w-3 h-3 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <template x-teleport="body">
                <div x-show="priorityOpen" x-transition @click.outside="priorityOpen = false" :style="priorityMenuStyle"
                     class="fixed z-[60] w-36 bg-white border border-slate-200 rounded-lg shadow-lg py-1">
                    <template x-for="opt in priorityOptions" :key="opt.value">
                        <button type="button" @click="setPriority(opt.value)"
                                class="w-full text-left px-3 py-1.5 text-xs hover:bg-slate-50 flex items-center gap-2"
                                :class="opt.value === priority ? 'font-semibold text-slate-800' : 'text-slate-600'">
                            <span class="w-2 h-2 rounded-full shrink-0" :class="priorityColors[opt.value].split(' ')[0]"></span>
                            <span x-text="opt.label"></span>
                        </button>
                    </template>
                </div>
            </template>
        @else
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium" :class="priorityColors[priority]" x-text="priorityLabel"></span>
        @endif
    </td>

    {{-- Due Date --}}
    <td class="px-4 py-3 text-slate-600 font-mono text-xs">
        @if($canEditTasks)
            <button type="button" x-ref="dueBtn"
                    @click="dueMenuStyle = menuStyle($refs.dueBtn); dueOpen = true; statusOpen = false; priorityOpen = false; assigneesOpen = false; $nextTick(() => { $refs.dueDateInput.focus(); $refs.dueDateInput.showPicker && $refs.dueDateInput.showPicker(); })"
                    class="hover:underline" :class="overdue ? 'text-red-600 font-semibold' : ''">
                <span x-text="dueDateDisplay"></span>
            </button>
            <template x-teleport="body">
                <div x-show="dueOpen" x-transition @click.outside="dueOpen = false" :style="dueMenuStyle"
                     class="fixed z-[60] bg-white border border-slate-200 rounded-lg shadow-lg p-3 flex items-center gap-2 font-sans">
                    <input type="date" x-ref="dueDateInput" x-model="dueDate"
                           class="px-2 py-1 border border-slate-300 rounded text-xs focus:outline-none focus:ring-2 focus:ring-[#E26B3D]/40">
                    <button type="button" @click="saveDueDate()" :disabled="saving"
                            class="px-2 py-1 text-xs bg-[#E26B3D] text-white rounded hover:bg-[#c85a2f] disabled:opacity-50">Save</button>
                    <button type="button" @click="dueOpen = false" class="px-2 py-1 text-xs text-slate-500 hover:text-slate-700">Cancel</button>
                </div>
            </template>
        @else
            <span x-text="dueDateDisplay" :class="overdue ? 'text-red-600 font-semibold' : ''"></span>
        @endif
    </td>

    {{-- Assignees --}}
    <td class="px-4 py-3 text-slate-600">
        @if($canEditTasks)
            <button type="button" x-ref="assigneesBtn"
                    @click="if (!assigneesOpen) assigneesMenuStyle = menuStyle($refs.assigneesBtn); assigneesOpen = !assigneesOpen; statusOpen = false; priorityOpen = false; dueOpen = false"
                    class="inline-flex items-center gap-1 text-xs hover:text-[#E26B3D] transition-colors max-w-[10rem]">
                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <span class="truncate" x-show="assignees.length" x-text="assignees.slice(0,2).map(a => a.name).join(', ') + (assignees.length > 2 ? ' +' + (assignees.length - 2) : '')"></span>
                <span class="text-slate-400" x-show="!assignees.length">Unassigned</span>
            </button>
            <template x-teleport="body">
                <div x-show="assigneesOpen" x-transition @click.outside="assigneesOpen = false" :style="assigneesMenuStyle"
                     class="fixed z-[60] w-56 max-h-64 overflow-y-auto bg-white border border-slate-200 rounded-lg shadow-lg py-1">
                    <template x-for="u in $store.taskUsers" :key="u.id">
                        <label class="flex items-center gap-2 px-3 py-1.5 text-xs hover:bg-slate-50 cursor-pointer">
                            <input type="checkbox" :checked="assigneeIds.includes(String(u.id))" @change="toggleAssignee(u.id)">
                            <span x-text="u.name"></span>
                        </label>
                    </template>
                </div>
            </template>
        @else
            <span class="inline-flex items-center gap-1 text-xs text-slate-500" x-show="assignees.length" x-text="assignees.slice(0,2).map(a => a.name).join(', ') + (assignees.length > 2 ? ' +' + (assignees.length - 2) : '')"></span>
            <span class="text-slate-400 text-xs" x-show="!assignees.length">—</span>
        @endif
    </td>

    <td class="px-4 py-3 text-right">
        <div class="flex items-center justify-end gap-2">
            <a href="{{ route('tasks.show', $task) }}"
               class="text-xs text-slate-500 hover:text-[#E26B3D] transition-colors px-2 py-1 rounded hover:bg-orange-50">
                View
            </a>
            @if($canEditTasks)
            <button @click="openEdit({
                        id:              {{ $task->id }},
                        project_id:      '{{ $task->project_id }}',
                        title:           {{ json_encode($task->title) }},
                        description:     {{ json_encode($task->description ?? '') }},
                        status:          status,
                        priority:        priority,
                        start_date:      '{{ $task->start_date?->format('Y-m-d') ?? '' }}',
                        due_date:        dueDate,
                        estimated_hours: '{{ $task->estimated_hours ?? '' }}',
                        assignees:       assigneeIds,
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

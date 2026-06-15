@extends('layouts.app')

@section('title', 'Work Schedules')
@section('page-title', 'Work Schedules')

@section('header-actions')
    @if(auth()->user()->hasPermission('create_work_schedules'))
    <button @click="$dispatch('panel:create')"
            class="inline-flex items-center gap-2 rounded-lg bg-[#E26B3D] px-4 py-2 text-sm font-mono font-medium text-white hover:bg-[#c8602a] transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        New Schedule
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
        name:                   '{{ old('name', '') }}',
        start_time:             '{{ old('start_time', '09:00') }}',
        end_time:               '{{ old('end_time', '17:00') }}',
        working_days:           {{ json_encode(old('working_days', ['Mon','Tue','Wed','Thu','Fri'])) }},
        break_duration_minutes: '{{ old('break_duration_minutes', '60') }}',
        description:            '{{ old('description', '') }}'
    },
    openCreate() {
        this.mode = 'create'; this.recordId = null; this.submitted = false;
        this.formData = { name: '', start_time: '09:00', end_time: '17:00', working_days: ['Mon','Tue','Wed','Thu','Fri'], break_duration_minutes: '60', description: '' };
        this.open = true;
    },
    openEdit(data) {
        this.mode = 'edit'; this.recordId = data.id; this.submitted = false;
        this.formData = data; this.open = true;
    },
    close() { this.open = false; this.submitted = false; }
}" @panel:create.window="openCreate()">

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-stone-50 border-b border-slate-200">
                <tr>
                    <th class="text-left px-5 py-3.5 text-xs font-mono font-medium text-slate-500 uppercase tracking-wider">Name</th>
                    <th class="text-left px-5 py-3.5 text-xs font-mono font-medium text-slate-500 uppercase tracking-wider">Hours</th>
                    <th class="text-left px-5 py-3.5 text-xs font-mono font-medium text-slate-500 uppercase tracking-wider">Working Days</th>
                    <th class="text-left px-5 py-3.5 text-xs font-mono font-medium text-slate-500 uppercase tracking-wider">Break</th>
                    <th class="text-right px-5 py-3.5 text-xs font-mono font-medium text-slate-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @php
                    $canEditWS   = auth()->user()->hasPermission('edit_work_schedules');
                    $canDeleteWS = auth()->user()->hasPermission('delete_work_schedules');
                @endphp
                @forelse($schedules as $sch)
                    <tr class="hover:bg-stone-50/60 transition-colors">
                        <td class="px-5 py-4">
                            <p class="font-medium text-slate-800">{{ $sch->name }}</p>
                            @if($sch->description)
                                <p class="text-xs text-slate-400 mt-0.5 truncate max-w-[200px]">{{ $sch->description }}</p>
                            @endif
                        </td>
                        <td class="px-5 py-4 font-mono text-xs text-slate-700">{{ $sch->start_time }} – {{ $sch->end_time }}</td>
                        <td class="px-5 py-4">
                            <div class="flex flex-wrap gap-1">
                                @foreach($sch->working_days as $day)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-mono font-medium bg-slate-100 text-slate-600">{{ $day }}</span>
                                @endforeach
                            </div>
                        </td>
                        <td class="px-5 py-4 font-mono text-xs text-slate-600">{{ $sch->break_duration_minutes }} min</td>
                        <td class="px-5 py-4 text-right">
                            <div class="inline-flex items-center gap-1.5">
                                @if($canEditWS)
                                <button @click="openEdit({
                                            id:                     {{ $sch->id }},
                                            name:                   '{{ e($sch->name) }}',
                                            start_time:             '{{ $sch->start_time }}',
                                            end_time:               '{{ $sch->end_time }}',
                                            working_days:           {{ json_encode($sch->working_days) }},
                                            break_duration_minutes: '{{ $sch->break_duration_minutes }}',
                                            description:            `{{ e($sch->description ?? '') }}`
                                        })"
                                        class="p-1.5 rounded-lg text-slate-400 hover:text-[#E26B3D] hover:bg-[#E26B3D]/10 transition-colors" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </button>
                                @endif
                                @if($canDeleteWS)
                                <button @click="$dispatch('confirm:delete', { action: '{{ route('work-schedules.destroy', $sch) }}' })"
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
                        <td colspan="5" class="px-5 py-14 text-center">
                            <p class="text-slate-400 font-mono text-sm">No work schedules defined yet.</p>
                            @if(auth()->user()->hasPermission('create_work_schedules'))
                            <button @click="$dispatch('panel:create')" class="mt-3 text-sm text-[#E26B3D] hover:underline font-mono">Create the first one</button>
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
        @if($schedules->hasPages())
            <div class="px-5 py-3 border-t border-slate-100">{{ $schedules->links() }}</div>
        @endif
    </div>

    {{-- Backdrop --}}
    <div x-show="open" @click="close()"
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-black/40 z-40" style="display:none;"></div>

    {{-- Slide-over --}}
    <div x-show="open"
         x-transition:enter="transition ease-out duration-300" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
         x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
         class="fixed right-0 top-0 h-full w-full max-w-lg bg-white shadow-2xl z-50 flex flex-col" style="display:none;">

        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 shrink-0">
            <h2 class="text-base font-semibold text-slate-800" x-text="mode === 'create' ? 'New Work Schedule' : 'Edit Work Schedule'"></h2>
            <button @click="close()" class="p-1.5 rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        @if($errors->any())
            <div class="mx-6 mt-4 p-3 rounded-lg bg-red-50 border border-red-200">
                <ul class="text-xs text-red-600 space-y-0.5">
                    @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
                </ul>
            </div>
        @endif

        <form method="POST"
              :action="mode === 'create' ? '{{ route('work-schedules.store') }}' : '{{ url('work-schedules') }}/' + recordId"
              @submit="submitted = true"
              class="flex-1 overflow-y-auto flex flex-col">
            @csrf
            <input type="hidden" name="_mode" :value="mode">
            <input type="hidden" name="record_id" :value="recordId">

            <div class="px-6 py-6 space-y-6 flex-1">
                <div>
                    <h3 class="text-xs font-mono font-semibold text-slate-400 uppercase tracking-widest mb-4">Schedule Details</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1.5">Name <span class="text-red-500">*</span></label>
                            <input type="text" name="name" :value="formData.name"
                                   placeholder="e.g. Standard 9–5"
                                   class="w-full text-sm border rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#E26B3D]/40 focus:border-[#E26B3D] text-slate-700 placeholder-slate-400 transition-colors"
                                   :class="submitted && !formData.name ? 'border-red-400 ring-1 ring-red-400' : 'border-slate-300'">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1.5">Start Time <span class="text-red-500">*</span></label>
                                <input type="time" name="start_time" :value="formData.start_time"
                                       class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#E26B3D]/40 focus:border-[#E26B3D] text-slate-700 font-mono transition-colors">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1.5">End Time <span class="text-red-500">*</span></label>
                                <input type="time" name="end_time" :value="formData.end_time"
                                       class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#E26B3D]/40 focus:border-[#E26B3D] text-slate-700 font-mono transition-colors">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1.5">Break Duration (minutes)</label>
                            <input type="number" name="break_duration_minutes" :value="formData.break_duration_minutes" min="0"
                                   placeholder="60"
                                   class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#E26B3D]/40 focus:border-[#E26B3D] text-slate-700 font-mono transition-colors">
                        </div>
                    </div>
                </div>

                <div class="border-t border-slate-100"></div>

                <div>
                    <h3 class="text-xs font-mono font-semibold text-slate-400 uppercase tracking-widest mb-4">Working Days <span class="text-red-500">*</span></h3>
                    <div class="grid grid-cols-4 gap-2">
                        @foreach(['Mon','Tue','Wed','Thu','Fri','Sat','Sun'] as $day)
                        <label class="flex flex-col items-center gap-1.5 cursor-pointer group">
                            <input type="checkbox" name="working_days[]" value="{{ $day }}"
                                   x-model="formData.working_days"
                                   class="sr-only peer">
                            <div class="w-full py-2 rounded-lg border text-center text-xs font-mono font-medium transition-colors
                                        peer-checked:bg-[#E26B3D] peer-checked:text-white peer-checked:border-[#E26B3D]
                                        border-slate-200 text-slate-500 group-hover:border-slate-300">
                                {{ $day }}
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>

                <div class="border-t border-slate-100"></div>

                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1.5">Description <span class="text-slate-400 font-normal">(optional)</span></label>
                    <textarea name="description" rows="2"
                              x-effect="$el.value = formData.description"
                              placeholder="Any additional notes about this schedule..."
                              class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#E26B3D]/40 focus:border-[#E26B3D] text-slate-700 placeholder-slate-400 resize-none transition-colors"></textarea>
                </div>
            </div>

            <div class="px-6 py-4 border-t border-slate-100 shrink-0 flex gap-3">
                <button type="submit"
                        class="flex-1 bg-[#E26B3D] hover:bg-[#c8602a] text-white text-sm font-medium py-2.5 rounded-lg transition-colors font-mono"
                        x-text="mode === 'create' ? 'Create Schedule' : 'Save Changes'"></button>
                <button type="button" @click="close()"
                        class="px-5 py-2.5 text-sm font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors font-mono">Cancel</button>
            </div>
        </form>
    </div>
</div>
@endsection

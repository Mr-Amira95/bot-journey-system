@extends('layouts.app')

@section('title', 'Attendance')
@section('page-title', 'Attendance')

@section('header-actions')
@php $lastType = $todayLast?->type->value ?? null; @endphp

    {{-- Today's status indicator --}}
    <div class="flex items-center gap-1.5 mr-1">
        @if($lastType === 'check_in' || $lastType === 'break_end')
            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse shrink-0"></span>
            <span class="text-xs font-mono text-slate-500 hidden sm:inline">
                Clocked in{{ $todayLast ? ' · ' . $todayLast->time_date->format('H:i') : '' }}
            </span>
        @elseif($lastType === 'break_start')
            <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse shrink-0"></span>
            <span class="text-xs font-mono text-slate-500 hidden sm:inline">
                On break{{ $todayLast ? ' · ' . $todayLast->time_date->format('H:i') : '' }}
            </span>
        @elseif($lastType === 'check_out')
            <span class="w-2 h-2 rounded-full bg-slate-400 shrink-0"></span>
            <span class="text-xs font-mono text-slate-500 hidden sm:inline">Checked out</span>
        @else
            <span class="w-2 h-2 rounded-full bg-slate-300 shrink-0"></span>
            <span class="text-xs font-mono text-slate-400 hidden sm:inline">Not clocked in</span>
        @endif
    </div>

    {{-- Clock action buttons --}}
    @if(!$lastType || $lastType === 'check_out')
        <form method="POST" action="{{ route('attendance.clock-in') }}" class="inline">
            @csrf
            <button type="submit"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-500 hover:bg-emerald-600 px-3 py-2 text-sm font-mono font-medium text-white transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                </svg>
                Check In
            </button>
        </form>
    @endif

    @if($lastType === 'check_in' || $lastType === 'break_end')
        <form method="POST" action="{{ route('attendance.break-start') }}" class="inline">
            @csrf
            <button type="submit"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-amber-400 hover:bg-amber-500 px-3 py-2 text-sm font-mono font-medium text-white transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Break
            </button>
        </form>
        <form method="POST" action="{{ route('attendance.clock-out') }}" class="inline">
            @csrf
            <button type="submit"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-slate-600 hover:bg-slate-700 px-3 py-2 text-sm font-mono font-medium text-white transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                </svg>
                Check Out
            </button>
        </form>
    @endif

    @if($lastType === 'break_start')
        <form method="POST" action="{{ route('attendance.break-end') }}" class="inline">
            @csrf
            <button type="submit"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-blue-500 hover:bg-blue-600 px-3 py-2 text-sm font-mono font-medium text-white transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                End Break
            </button>
        </form>
    @endif

    @if(auth()->user()->hasPermission('create_attendance'))
        <button @click="$dispatch('panel:create')"
                class="inline-flex items-center gap-2 rounded-lg bg-[#E26B3D] px-4 py-2 text-sm font-mono font-medium text-white hover:bg-[#c8602a] transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add Record
        </button>
    @endif
@endsection

@section('content')
@php
    $typeColors = [
        'check_in'    => 'bg-emerald-100 text-emerald-700',
        'check_out'   => 'bg-slate-100 text-slate-600',
        'break_start' => 'bg-amber-100 text-amber-700',
        'break_end'   => 'bg-blue-100 text-blue-700',
    ];
    $typeLabels = [
        'check_in'    => 'Check In',
        'check_out'   => 'Check Out',
        'break_start' => 'Break Start',
        'break_end'   => 'Break End',
    ];
@endphp

<div x-data="{
    open: {{ $errors->any() ? 'true' : 'false' }},
    mode: '{{ old('_mode', 'create') }}',
    recordId: {{ old('record_id', 'null') }},
    submitted: false,
    formData: {
        user_id:   '{{ old('user_id', auth()->id()) }}',
        type:      '{{ old('type', '') }}',
        time_date: '{{ old('time_date', '') }}',
        notes:     '{{ old('notes', '') }}'
    },
    openCreate() {
        this.mode = 'create'; this.recordId = null; this.submitted = false;
        this.formData = { user_id: '{{ auth()->id() }}', type: '', time_date: '', notes: '' };
        this.open = true;
    },
    openEdit(data) {
        this.mode = 'edit'; this.recordId = data.id; this.submitted = false;
        this.formData = data; this.open = true;
    },
    close() { this.open = false; this.submitted = false; }
}" @panel:create.window="openCreate()">

    {{-- Tabs --}}
    @if($canViewAll)
    <div class="flex items-center gap-1 bg-slate-100 rounded-xl p-1 w-fit mb-5">
        <a href="{{ route('attendance.index', ['tab' => 'mine']) }}"
           class="px-4 py-2 rounded-lg text-sm font-medium transition-all {{ $tab === 'mine' ? 'bg-white shadow text-slate-800' : 'text-slate-500 hover:text-slate-700' }}">
            My Attendance
        </a>
        <a href="{{ route('attendance.index', ['tab' => 'all']) }}"
           class="px-4 py-2 rounded-lg text-sm font-medium transition-all {{ $tab === 'all' ? 'bg-white shadow text-slate-800' : 'text-slate-500 hover:text-slate-700' }}">
            All Employees
        </a>
    </div>
    @endif

    {{-- Filters --}}
    <div class="mb-5">
        <form method="GET" action="{{ route('attendance.index') }}" class="flex flex-wrap items-center gap-3">
            <input type="hidden" name="tab" value="{{ $tab }}">

            <select name="type" class="py-2 pl-3 pr-8 rounded-lg border border-slate-300 bg-white text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#E26B3D] font-mono">
                <option value="">All Types</option>
                @foreach($types as $t)
                    <option value="{{ $t->value }}" {{ request('type') === $t->value ? 'selected' : '' }}>{{ $typeLabels[$t->value] }}</option>
                @endforeach
            </select>

            @if($canViewAll)
            <select name="user_id" class="py-2 pl-3 pr-8 rounded-lg border border-slate-300 bg-white text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#E26B3D] font-mono">
                <option value="">All Employees</option>
                @foreach($users as $u)
                    <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                @endforeach
            </select>
            @endif

            <input type="date" name="date_from" value="{{ request('date_from') }}"
                   class="py-2 px-3 rounded-lg border border-slate-300 bg-white text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#E26B3D] font-mono">
            <input type="date" name="date_to" value="{{ request('date_to') }}"
                   class="py-2 px-3 rounded-lg border border-slate-300 bg-white text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#E26B3D] font-mono">

            <button type="submit" class="px-4 py-2 rounded-lg bg-white border border-slate-300 text-sm text-slate-700 hover:bg-stone-50 transition-colors font-mono">Filter</button>
            @if(request()->hasAny(['type', 'user_id', 'date_from', 'date_to']))
                <a href="{{ route('attendance.index', ['tab' => $tab]) }}" class="text-sm text-slate-500 hover:text-slate-700 font-mono">Clear</a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-stone-50 border-b border-slate-200">
                <tr>
                    @if($tab === 'all')<th class="text-left px-5 py-3.5 text-xs font-mono font-medium text-slate-500 uppercase tracking-wider">Employee</th>@endif
                    <th class="text-left px-5 py-3.5 text-xs font-mono font-medium text-slate-500 uppercase tracking-wider">Type</th>
                    <th class="text-left px-5 py-3.5 text-xs font-mono font-medium text-slate-500 uppercase tracking-wider">Date</th>
                    <th class="text-left px-5 py-3.5 text-xs font-mono font-medium text-slate-500 uppercase tracking-wider">Time</th>
                    <th class="text-left px-5 py-3.5 text-xs font-mono font-medium text-slate-500 uppercase tracking-wider">Notes</th>
                    @if(auth()->user()->hasPermission('create_attendance'))<th class="text-right px-5 py-3.5 text-xs font-mono font-medium text-slate-500 uppercase tracking-wider">Actions</th>@endif
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @php $canManageAttendance = auth()->user()->hasPermission('create_attendance'); @endphp
                @forelse($records as $record)
                    @php $tc = $typeColors[$record->type->value] ?? 'bg-slate-100 text-slate-600'; @endphp
                    <tr class="hover:bg-stone-50/60 transition-colors">
                        @if($tab === 'all')
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-full bg-[#E26B3D] flex items-center justify-center text-white text-xs font-semibold shrink-0">
                                    {{ strtoupper(substr($record->user?->name ?? '?', 0, 1)) }}
                                </div>
                                <span class="font-medium text-slate-800">{{ $record->user?->name ?? '—' }}</span>
                            </div>
                        </td>
                        @endif
                        <td class="px-5 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-mono font-medium {{ $tc }}">
                                {{ $typeLabels[$record->type->value] ?? $record->type->value }}
                            </span>
                        </td>
                        <td class="px-5 py-4 font-mono text-xs text-slate-700">{{ $record->time_date->format('M d, Y') }}</td>
                        <td class="px-5 py-4 font-mono text-sm text-slate-800 font-medium">{{ $record->time_date->format('H:i') }}</td>
                        <td class="px-5 py-4 text-slate-500 text-sm max-w-xs">{{ $record->notes ?? '—' }}</td>
                        @if($canManageAttendance)
                        <td class="px-5 py-4 text-right">
                            <div class="inline-flex items-center gap-1.5">
                                <button @click="openEdit({
                                            id:        {{ $record->id }},
                                            user_id:   '{{ $record->user_id }}',
                                            type:      '{{ $record->type->value }}',
                                            time_date: '{{ $record->time_date->format('Y-m-d\TH:i') }}',
                                            notes:     `{{ e($record->notes ?? '') }}`
                                        })"
                                        class="p-1.5 rounded-lg text-slate-400 hover:text-[#E26B3D] hover:bg-[#E26B3D]/10 transition-colors" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </button>
                                <button @click="$dispatch('confirm:delete', { action: '{{ route('attendance.destroy', $record) }}' })"
                                        class="p-1.5 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ ($tab === 'all' ? 1 : 0) + ($canManageAttendance ? 1 : 0) + 4 }}" class="px-5 py-14 text-center">
                            <p class="text-slate-400 font-mono text-sm">No attendance records found.</p>
                            @if($canManageAttendance)
                                <button @click="$dispatch('panel:create')" class="mt-3 text-sm text-[#E26B3D] hover:underline font-mono">Add the first record</button>
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
        @if($records->hasPages())
            <div class="px-5 py-3 border-t border-slate-100">{{ $records->links() }}</div>
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
            <h2 class="text-base font-semibold text-slate-800" x-text="mode === 'create' ? 'Add Attendance Record' : 'Edit Attendance Record'"></h2>
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
              :action="mode === 'create' ? '{{ route('attendance.store') }}' : '{{ url('attendance') }}/' + recordId"
              @submit="submitted = true"
              class="flex-1 overflow-y-auto flex flex-col">
            @csrf
            <input type="hidden" name="_mode" :value="mode">
            <input type="hidden" name="record_id" :value="recordId">

            <div class="px-6 py-6 space-y-5 flex-1">
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1.5">Employee <span class="text-red-500">*</span></label>
                    <select name="user_id"
                            x-effect="$el.value = formData.user_id"
                            @change="formData.user_id = $event.target.value"
                            class="w-full text-sm border rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#E26B3D]/40 focus:border-[#E26B3D] text-slate-700 bg-white transition-colors"
                            :class="submitted && !formData.user_id ? 'border-red-400 ring-1 ring-red-400' : 'border-slate-300'">
                        <option value="">Select employee...</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1.5">Type <span class="text-red-500">*</span></label>
                    <select name="type"
                            x-effect="$el.value = formData.type"
                            @change="formData.type = $event.target.value"
                            class="w-full text-sm border rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#E26B3D]/40 focus:border-[#E26B3D] text-slate-700 bg-white transition-colors"
                            :class="submitted && !formData.type ? 'border-red-400 ring-1 ring-red-400' : 'border-slate-300'">
                        <option value="">Select type...</option>
                        @foreach($types as $t)
                            <option value="{{ $t->value }}">{{ $typeLabels[$t->value] }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1.5">Date & Time <span class="text-red-500">*</span></label>
                    <input type="datetime-local" name="time_date" :value="formData.time_date"
                           class="w-full text-sm border rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#E26B3D]/40 focus:border-[#E26B3D] text-slate-700 transition-colors"
                           :class="submitted && !formData.time_date ? 'border-red-400 ring-1 ring-red-400' : 'border-slate-300'">
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1.5">Notes <span class="text-slate-400 font-normal">(optional)</span></label>
                    <textarea name="notes" rows="3"
                              x-effect="$el.value = formData.notes"
                              placeholder="Optional note..."
                              class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#E26B3D]/40 focus:border-[#E26B3D] text-slate-700 placeholder-slate-400 resize-none transition-colors"></textarea>
                </div>
            </div>

            <div class="px-6 py-4 border-t border-slate-100 shrink-0 flex gap-3">
                <button type="submit"
                        class="flex-1 bg-[#E26B3D] hover:bg-[#c8602a] text-white text-sm font-medium py-2.5 rounded-lg transition-colors font-mono"
                        x-text="mode === 'create' ? 'Add Record' : 'Save Changes'"></button>
                <button type="button" @click="close()"
                        class="px-5 py-2.5 text-sm font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors font-mono">Cancel</button>
            </div>
        </form>
    </div>
</div>
@endsection

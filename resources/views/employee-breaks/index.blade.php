@extends('layouts.app')

@section('title', 'Breaks')
@section('page-title', 'Employee Breaks')

@section('header-actions')
    @if(auth()->user()->hasPermission('create_employee_breaks'))
    <button @click="$dispatch('panel:create')"
            class="inline-flex items-center gap-2 rounded-lg bg-[#E26B3D] px-4 py-2 text-sm font-mono font-medium text-white hover:bg-[#c8602a] transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Log Break
    </button>
    @endif
@endsection

@section('content')
@if($canViewAll)
<div class="flex items-center gap-1 bg-slate-100 rounded-xl p-1 w-fit mb-5">
    <a href="{{ route('employee-breaks.index', ['tab' => 'mine']) }}"
       class="px-4 py-2 rounded-lg text-sm font-medium transition-all {{ $tab === 'mine' ? 'bg-white shadow text-slate-800' : 'text-slate-500 hover:text-slate-700' }}">
        My Breaks
    </a>
    <a href="{{ route('employee-breaks.index', ['tab' => 'all']) }}"
       class="px-4 py-2 rounded-lg text-sm font-medium transition-all {{ $tab === 'all' ? 'bg-white shadow text-slate-800' : 'text-slate-500 hover:text-slate-700' }}">
        All Breaks
    </a>
</div>
@endif

<div x-data="{
    open: {{ $errors->any() ? 'true' : 'false' }},
    mode: '{{ old('_mode', 'create') }}',
    recordId: {{ old('record_id', 'null') }},
    submitted: false,
    formData: {
        started_at: '{{ old('started_at', '') }}',
        ended_at:   '{{ old('ended_at', '') }}',
        type:       '{{ old('type', 'short_break') }}',
        notes:      '{{ old('notes', '') }}'
    },
    openCreate() {
        this.mode = 'create'; this.recordId = null; this.submitted = false;
        this.formData = { started_at: '', ended_at: '', type: 'short_break', notes: '' };
        this.open = true;
    },
    openEdit(data) {
        this.mode = 'edit'; this.recordId = data.id; this.submitted = false;
        this.formData = data; this.open = true;
    },
    close() { this.open = false; this.submitted = false; }
}" @panel:create.window="openCreate()">

    {{-- Filters --}}
    <div class="mb-5">
        <form method="GET" action="{{ route('employee-breaks.index') }}" class="flex flex-wrap items-center gap-3">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <select name="type" class="py-2 pl-3 pr-8 rounded-lg border border-slate-300 bg-white text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#E26B3D] font-mono">
                <option value="">All Types</option>
                @foreach($breakTypes as $bt)
                    <option value="{{ $bt->value }}" {{ request('type') === $bt->value ? 'selected' : '' }}>{{ ucwords(str_replace('_', ' ', $bt->value)) }}</option>
                @endforeach
            </select>
            @if($tab === 'all')
            <select name="user_id" class="py-2 pl-3 pr-8 rounded-lg border border-slate-300 bg-white text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#E26B3D] font-mono">
                <option value="">All Employees</option>
                @foreach($users as $u)
                    <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                @endforeach
            </select>
            @endif
            <button type="submit" class="px-4 py-2 rounded-lg bg-white border border-slate-300 text-sm text-slate-700 hover:bg-stone-50 transition-colors font-mono">Filter</button>
            @if(request()->hasAny(['type', 'user_id']))
                <a href="{{ route('employee-breaks.index', ['tab' => $tab]) }}" class="text-sm text-slate-500 hover:text-slate-700 font-mono">Clear</a>
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
                    <th class="text-left px-5 py-3.5 text-xs font-mono font-medium text-slate-500 uppercase tracking-wider">Started</th>
                    <th class="text-left px-5 py-3.5 text-xs font-mono font-medium text-slate-500 uppercase tracking-wider">Ended</th>
                    <th class="text-left px-5 py-3.5 text-xs font-mono font-medium text-slate-500 uppercase tracking-wider">Duration</th>
                    <th class="text-right px-5 py-3.5 text-xs font-mono font-medium text-slate-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @php
                    $typeColors = [
                        'lunch'       => 'bg-blue-100 text-blue-700',
                        'short_break' => 'bg-slate-100 text-slate-600',
                        'other'       => 'bg-purple-100 text-purple-700',
                    ];
                @endphp
                @php
                    $canEditBreaks   = auth()->user()->hasPermission('create_employee_breaks');
                    $canDeleteBreaks = auth()->user()->hasPermission('delete_employee_breaks');
                @endphp
                @forelse($breaks as $brk)
                    @php
                        $tc  = $typeColors[$brk->type->value] ?? 'bg-slate-100 text-slate-600';
                        $dur = $brk->ended_at
                            ? $brk->started_at->diffInMinutes($brk->ended_at) . ' min'
                            : '—';
                    @endphp
                    <tr class="hover:bg-stone-50/60 transition-colors">
                        @if($tab === 'all')
                        <td class="px-5 py-4 font-medium text-slate-800">{{ $brk->user->name }}</td>
                        @endif
                        <td class="px-5 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-mono font-medium {{ $tc }}">
                                {{ ucwords(str_replace('_', ' ', $brk->type->value)) }}
                            </span>
                        </td>
                        <td class="px-5 py-4 font-mono text-xs text-slate-700">{{ $brk->started_at->format('M d, Y H:i') }}</td>
                        <td class="px-5 py-4 font-mono text-xs text-slate-700">
                            {{ $brk->ended_at ? $brk->ended_at->format('H:i') : '<span class="text-amber-600">In progress</span>' }}
                        </td>
                        <td class="px-5 py-4 font-mono text-slate-700 text-xs">{{ $dur }}</td>
                        <td class="px-5 py-4 text-right">
                            <div class="inline-flex items-center gap-1.5">
                                @if($canEditBreaks)
                                <button @click="openEdit({
                                            id:         {{ $brk->id }},
                                            started_at: '{{ $brk->started_at->format('Y-m-d\TH:i') }}',
                                            ended_at:   '{{ $brk->ended_at ? $brk->ended_at->format('Y-m-d\TH:i') : '' }}',
                                            type:       '{{ $brk->type->value }}',
                                            notes:      `{{ e($brk->notes ?? '') }}`
                                        })"
                                        class="p-1.5 rounded-lg text-slate-400 hover:text-[#E26B3D] hover:bg-[#E26B3D]/10 transition-colors" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </button>
                                @endif
                                @if($canDeleteBreaks)
                                <button @click="$dispatch('confirm:delete', { action: '{{ route('employee-breaks.destroy', $brk) }}' })"
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
                        <td colspan="{{ $tab === 'all' ? 6 : 5 }}" class="px-5 py-14 text-center">
                            <p class="text-slate-400 font-mono text-sm">No breaks logged yet.</p>
                            @if(auth()->user()->hasPermission('create_employee_breaks'))
                            <button @click="$dispatch('panel:create')" class="mt-3 text-sm text-[#E26B3D] hover:underline font-mono">Log the first break</button>
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
        @if($breaks->hasPages())
            <div class="px-5 py-3 border-t border-slate-100">{{ $breaks->links() }}</div>
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
            <h2 class="text-base font-semibold text-slate-800" x-text="mode === 'create' ? 'Log Break' : 'Edit Break'"></h2>
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
              :action="mode === 'create' ? '{{ route('employee-breaks.store') }}' : '{{ url('employee-breaks') }}/' + recordId"
              @submit="submitted = true"
              class="flex-1 overflow-y-auto flex flex-col">
            @csrf
            <input type="hidden" name="_mode" :value="mode">
            <input type="hidden" name="record_id" :value="recordId">

            <div class="px-6 py-6 space-y-6 flex-1">
                <div>
                    <h3 class="text-xs font-mono font-semibold text-slate-400 uppercase tracking-widest mb-4">Break Details</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1.5">Type <span class="text-red-500">*</span></label>
                            <select name="type"
                                    x-effect="$el.value = formData.type"
                                    @change="formData.type = $event.target.value"
                                    class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#E26B3D]/40 focus:border-[#E26B3D] text-slate-700 bg-white transition-colors">
                                @foreach($breakTypes as $bt)
                                    <option value="{{ $bt->value }}">{{ ucwords(str_replace('_', ' ', $bt->value)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1.5">Started At <span class="text-red-500">*</span></label>
                            <input type="datetime-local" name="started_at" :value="formData.started_at"
                                   class="w-full text-sm border rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#E26B3D]/40 focus:border-[#E26B3D] text-slate-700 font-mono transition-colors"
                                   :class="submitted && !formData.started_at ? 'border-red-400 ring-1 ring-red-400' : 'border-slate-300'">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1.5">Ended At <span class="text-slate-400 font-normal">(leave blank if still on break)</span></label>
                            <input type="datetime-local" name="ended_at" :value="formData.ended_at"
                                   class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#E26B3D]/40 focus:border-[#E26B3D] text-slate-700 font-mono transition-colors">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1.5">Notes <span class="text-slate-400 font-normal">(optional)</span></label>
                            <textarea name="notes" rows="2"
                                      x-effect="$el.value = formData.notes"
                                      placeholder="Any notes..."
                                      class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#E26B3D]/40 focus:border-[#E26B3D] text-slate-700 placeholder-slate-400 resize-none transition-colors"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="px-6 py-4 border-t border-slate-100 shrink-0 flex gap-3">
                <button type="submit"
                        class="flex-1 bg-[#E26B3D] hover:bg-[#c8602a] text-white text-sm font-medium py-2.5 rounded-lg transition-colors font-mono"
                        x-text="mode === 'create' ? 'Log Break' : 'Save Changes'"></button>
                <button type="button" @click="close()"
                        class="px-5 py-2.5 text-sm font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors font-mono">Cancel</button>
            </div>
        </form>
    </div>
</div>
@endsection

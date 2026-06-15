@extends('layouts.app')

@section('title', 'Leave Balances')
@section('page-title', 'Leave Balances')

@section('header-actions')
    @if(auth()->user()->hasPermission('create_leave_balances'))
    <button @click="$dispatch('panel:create')"
            class="inline-flex items-center gap-2 rounded-lg bg-[#E26B3D] px-4 py-2 text-sm font-mono font-medium text-white hover:bg-[#c8602a] transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Set Balance
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
        employee_id:    '{{ old('employee_id', '') }}',
        leave_type_id:  '{{ old('leave_type_id', '') }}',
        year:           '{{ old('year', now()->year) }}',
        allocated_days: '{{ old('allocated_days', '') }}',
        used_days:      '{{ old('used_days', '0') }}'
    },
    openCreate() {
        this.mode = 'create'; this.recordId = null; this.submitted = false;
        this.formData = { employee_id: '', leave_type_id: '', year: '{{ now()->year }}', allocated_days: '', used_days: '0' };
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
        <form method="GET" action="{{ route('leave-balances.index') }}" class="flex flex-wrap items-center gap-3">
            <select name="employee_id" class="py-2 pl-3 pr-8 rounded-lg border border-slate-300 bg-white text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#E26B3D] font-mono">
                <option value="">All Employees</option>
                @foreach($employees as $emp)
                    <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>{{ $emp->user->name }}</option>
                @endforeach
            </select>
            <select name="leave_type_id" class="py-2 pl-3 pr-8 rounded-lg border border-slate-300 bg-white text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#E26B3D] font-mono">
                <option value="">All Leave Types</option>
                @foreach($leaveTypes as $lt)
                    <option value="{{ $lt->id }}" {{ request('leave_type_id') == $lt->id ? 'selected' : '' }}>{{ $lt->name }}</option>
                @endforeach
            </select>
            <select name="year" class="py-2 pl-3 pr-8 rounded-lg border border-slate-300 bg-white text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#E26B3D] font-mono">
                <option value="">All Years</option>
                @foreach($years as $y)
                    <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 rounded-lg bg-white border border-slate-300 text-sm text-slate-700 hover:bg-stone-50 transition-colors font-mono">Filter</button>
            @if(request()->hasAny(['employee_id', 'leave_type_id', 'year']))
                <a href="{{ route('leave-balances.index') }}" class="text-sm text-slate-500 hover:text-slate-700 font-mono">Clear</a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-stone-50 border-b border-slate-200">
                <tr>
                    <th class="text-left px-5 py-3.5 text-xs font-mono font-medium text-slate-500 uppercase tracking-wider">Employee</th>
                    <th class="text-left px-5 py-3.5 text-xs font-mono font-medium text-slate-500 uppercase tracking-wider">Leave Type</th>
                    <th class="text-left px-5 py-3.5 text-xs font-mono font-medium text-slate-500 uppercase tracking-wider">Year</th>
                    <th class="text-left px-5 py-3.5 text-xs font-mono font-medium text-slate-500 uppercase tracking-wider">Allocated</th>
                    <th class="text-left px-5 py-3.5 text-xs font-mono font-medium text-slate-500 uppercase tracking-wider">Used</th>
                    <th class="text-left px-5 py-3.5 text-xs font-mono font-medium text-slate-500 uppercase tracking-wider">Remaining</th>
                    <th class="text-right px-5 py-3.5 text-xs font-mono font-medium text-slate-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @php
                    $canEditLB   = auth()->user()->hasPermission('edit_leave_balances');
                    $canDeleteLB = auth()->user()->hasPermission('delete_leave_balances');
                @endphp
                @forelse($balances as $bal)
                    @php $remaining = $bal->allocated_days - $bal->used_days; @endphp
                    <tr class="hover:bg-stone-50/60 transition-colors">
                        <td class="px-5 py-4 font-medium text-slate-800">{{ $bal->employee->user->name }}</td>
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-2">
                                @if($bal->leaveType->color)
                                    <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background:{{ $bal->leaveType->color }}"></span>
                                @endif
                                <span class="text-slate-700">{{ $bal->leaveType->name }}</span>
                            </div>
                        </td>
                        <td class="px-5 py-4 font-mono text-slate-700">{{ $bal->year }}</td>
                        <td class="px-5 py-4 font-mono text-slate-700">{{ $bal->allocated_days }} days</td>
                        <td class="px-5 py-4 font-mono text-slate-700">{{ $bal->used_days }} days</td>
                        <td class="px-5 py-4 font-mono font-medium {{ $remaining > 0 ? 'text-emerald-700' : 'text-red-600' }}">
                            {{ $remaining }} days
                        </td>
                        <td class="px-5 py-4 text-right">
                            <div class="inline-flex items-center gap-1.5">
                                @if($canEditLB)
                                <button @click="openEdit({
                                            id:             {{ $bal->id }},
                                            employee_id:    '{{ $bal->employee_id }}',
                                            leave_type_id:  '{{ $bal->leave_type_id }}',
                                            year:           '{{ $bal->year }}',
                                            allocated_days: '{{ $bal->allocated_days }}',
                                            used_days:      '{{ $bal->used_days }}'
                                        })"
                                        class="p-1.5 rounded-lg text-slate-400 hover:text-[#E26B3D] hover:bg-[#E26B3D]/10 transition-colors" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </button>
                                @endif
                                @if($canDeleteLB)
                                <button @click="$dispatch('confirm:delete', { action: '{{ route('leave-balances.destroy', $bal) }}' })"
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
                        <td colspan="7" class="px-5 py-14 text-center">
                            <p class="text-slate-400 font-mono text-sm">No leave balances found.</p>
                            @if(auth()->user()->hasPermission('create_leave_balances'))
                            <button @click="$dispatch('panel:create')" class="mt-3 text-sm text-[#E26B3D] hover:underline font-mono">Set the first balance</button>
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
        @if($balances->hasPages())
            <div class="px-5 py-3 border-t border-slate-100">{{ $balances->links() }}</div>
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
            <h2 class="text-base font-semibold text-slate-800" x-text="mode === 'create' ? 'Set Leave Balance' : 'Edit Leave Balance'"></h2>
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
              :action="mode === 'create' ? '{{ route('leave-balances.store') }}' : '{{ url('leave-balances') }}/' + recordId"
              @submit="submitted = true"
              class="flex-1 overflow-y-auto flex flex-col">
            @csrf
            <input type="hidden" name="_mode" :value="mode">
            <input type="hidden" name="record_id" :value="recordId">

            <div class="px-6 py-6 space-y-6 flex-1">
                <div>
                    <h3 class="text-xs font-mono font-semibold text-slate-400 uppercase tracking-widest mb-4">Assignment</h3>
                    <div class="space-y-4">
                        <div x-show="mode === 'create'">
                            <label class="block text-xs font-medium text-slate-600 mb-1.5">Employee <span class="text-red-500">*</span></label>
                            <select name="employee_id"
                                    x-effect="$el.value = formData.employee_id"
                                    @change="formData.employee_id = $event.target.value"
                                    class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#E26B3D]/40 focus:border-[#E26B3D] text-slate-700 bg-white transition-colors">
                                <option value="">Select employee...</option>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div x-show="mode === 'create'">
                            <label class="block text-xs font-medium text-slate-600 mb-1.5">Leave Type <span class="text-red-500">*</span></label>
                            <select name="leave_type_id"
                                    x-effect="$el.value = formData.leave_type_id"
                                    @change="formData.leave_type_id = $event.target.value"
                                    class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#E26B3D]/40 focus:border-[#E26B3D] text-slate-700 bg-white transition-colors">
                                <option value="">Select leave type...</option>
                                @foreach($leaveTypes as $lt)
                                    <option value="{{ $lt->id }}">{{ $lt->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div x-show="mode === 'create'">
                            <label class="block text-xs font-medium text-slate-600 mb-1.5">Year <span class="text-red-500">*</span></label>
                            <select name="year"
                                    x-effect="$el.value = formData.year"
                                    @change="formData.year = $event.target.value"
                                    class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#E26B3D]/40 focus:border-[#E26B3D] text-slate-700 bg-white transition-colors">
                                @foreach($years as $y)
                                    <option value="{{ $y }}">{{ $y }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="border-t border-slate-100"></div>

                <div>
                    <h3 class="text-xs font-mono font-semibold text-slate-400 uppercase tracking-widest mb-4">Days</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1.5">Allocated Days <span class="text-red-500">*</span></label>
                            <input type="number" name="allocated_days" :value="formData.allocated_days" step="0.5" min="0"
                                   placeholder="e.g. 21"
                                   class="w-full text-sm border rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#E26B3D]/40 focus:border-[#E26B3D] text-slate-700 font-mono transition-colors"
                                   :class="submitted && !formData.allocated_days ? 'border-red-400 ring-1 ring-red-400' : 'border-slate-300'">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1.5">Used Days</label>
                            <input type="number" name="used_days" :value="formData.used_days" step="0.5" min="0"
                                   placeholder="0"
                                   class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#E26B3D]/40 focus:border-[#E26B3D] text-slate-700 font-mono transition-colors">
                        </div>
                    </div>
                </div>
            </div>

            <div class="px-6 py-4 border-t border-slate-100 shrink-0 flex gap-3">
                <button type="submit"
                        class="flex-1 bg-[#E26B3D] hover:bg-[#c8602a] text-white text-sm font-medium py-2.5 rounded-lg transition-colors font-mono"
                        x-text="mode === 'create' ? 'Save Balance' : 'Update Balance'"></button>
                <button type="button" @click="close()"
                        class="px-5 py-2.5 text-sm font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors font-mono">Cancel</button>
            </div>
        </form>
    </div>
</div>
@endsection

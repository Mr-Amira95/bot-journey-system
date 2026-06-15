@extends('layouts.app')

@section('title', 'Payroll')
@section('page-title', 'Payroll')

@section('header-actions')
    @if(auth()->user()->hasPermission('create_payroll'))
    <button @click="$dispatch('panel:create')"
            class="inline-flex items-center gap-2 rounded-lg bg-[#E26B3D] px-4 py-2 text-sm font-mono font-medium text-white hover:bg-[#c8602a] transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        New Payroll Run
    </button>
    @endif
@endsection

@section('content')
<div x-data="{
    open: {{ $errors->any() ? 'true' : 'false' }},
    mode: 'create',
    submitted: false,
    periodStart: '{{ old('period_start', '') }}',
    periodEnd:   '{{ old('period_end', '') }}',
    notes:       '{{ old('notes', '') }}',
    items: @json(old('items', [])),
    openCreate() {
        this.mode = 'create';
        this.submitted = false;
        this.periodStart = '';
        this.periodEnd = '';
        this.notes = '';
        this.items = [];
        this.addItem();
        this.open = true;
    },
    addItem() {
        this.items.push({ employee_id: '', base_salary: '', bonuses: '', deductions: '', notes: '' });
    },
    removeItem(index) {
        this.items.splice(index, 1);
    },
    close() { this.open = false; this.submitted = false; }
}" @panel:create.window="openCreate()">

    {{-- Filters --}}
    <div class="mb-5">
        <form method="GET" action="{{ route('payroll.index') }}" class="flex flex-wrap items-center gap-3">
            <div class="relative flex-1 min-w-[200px] max-w-sm">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search payroll runs..."
                       class="w-full pl-9 pr-4 py-2 rounded-lg border border-slate-300 bg-white text-sm text-slate-800 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-[#E26B3D] font-mono">
            </div>
            <select name="status" class="py-2 pl-3 pr-8 rounded-lg border border-slate-300 bg-white text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#E26B3D] font-mono">
                <option value="">All Statuses</option>
                @foreach($statuses as $s)
                    <option value="{{ $s->value }}" {{ request('status') === $s->value ? 'selected' : '' }}>
                        {{ ucfirst($s->value) }}
                    </option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 rounded-lg bg-white border border-slate-300 text-sm text-slate-700 hover:bg-stone-50 transition-colors font-mono">Search</button>
            @if(request()->hasAny(['search', 'status']))
                <a href="{{ route('payroll.index') }}" class="text-sm text-slate-500 hover:text-slate-700 font-mono">Clear</a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-stone-50 border-b border-slate-200">
                <tr>
                    <th class="text-left px-5 py-3.5 text-xs font-mono font-medium text-slate-500 uppercase tracking-wider">Period</th>
                    <th class="text-left px-5 py-3.5 text-xs font-mono font-medium text-slate-500 uppercase tracking-wider">Employees</th>
                    <th class="text-left px-5 py-3.5 text-xs font-mono font-medium text-slate-500 uppercase tracking-wider">Total Net</th>
                    <th class="text-left px-5 py-3.5 text-xs font-mono font-medium text-slate-500 uppercase tracking-wider">Status</th>
                    <th class="text-left px-5 py-3.5 text-xs font-mono font-medium text-slate-500 uppercase tracking-wider">Created By</th>
                    <th class="text-right px-5 py-3.5 text-xs font-mono font-medium text-slate-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @php
                    $canApprovePayroll = auth()->user()->hasPermission('approve_payroll');
                    $canMarkPaid       = auth()->user()->hasPermission('mark_payroll_paid');
                    $canDeletePayroll  = auth()->user()->hasPermission('delete_payroll');
                @endphp
                @forelse($runs as $run)
                    @php
                        $statusColors = [
                            'draft'    => 'bg-slate-100 text-slate-600',
                            'approved' => 'bg-blue-100 text-blue-700',
                            'paid'     => 'bg-emerald-100 text-emerald-700',
                        ];
                        $sc = $statusColors[$run->status->value] ?? 'bg-slate-100 text-slate-600';
                        $totalNet = $run->items->sum('net_salary');
                    @endphp
                    <tr class="hover:bg-stone-50/60 transition-colors">
                        <td class="px-5 py-4">
                            <p class="font-medium text-slate-800 font-mono">{{ $run->period_start->format('M d') }} — {{ $run->period_end->format('M d, Y') }}</p>
                            @if($run->notes)
                                <p class="text-xs text-slate-400 mt-0.5 truncate max-w-[200px]">{{ $run->notes }}</p>
                            @endif
                        </td>
                        <td class="px-5 py-4 font-mono text-slate-700">
                            {{ $run->items->count() }} employees
                        </td>
                        <td class="px-5 py-4 font-mono font-medium text-slate-800">
                            ${{ number_format($totalNet, 2) }}
                        </td>
                        <td class="px-5 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-mono font-medium {{ $sc }}">
                                {{ ucfirst($run->status->value) }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-xs text-slate-600">
                            {{ $run->createdBy?->name ?? '—' }}
                        </td>
                        <td class="px-5 py-4 text-right">
                            <div class="inline-flex items-center gap-1.5">
                                <a href="{{ route('reports.payroll', $run) }}"
                                   target="_blank"
                                   class="p-1.5 rounded-lg text-slate-400 hover:text-blue-600 hover:bg-blue-50 transition-colors"
                                   title="Download PDF">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                    </svg>
                                </a>
                                @if($run->status->value === 'draft')
                                    @if($canApprovePayroll)
                                    <form action="{{ route('payroll.approve', $run) }}" method="POST">
                                        @csrf
                                        <button type="submit"
                                                class="px-2.5 py-1 rounded-lg text-xs font-mono font-medium text-blue-600 bg-blue-50 hover:bg-blue-100 transition-colors">
                                            Approve
                                        </button>
                                    </form>
                                    @endif
                                @elseif($run->status->value === 'approved')
                                    @if($canMarkPaid)
                                    <form action="{{ route('payroll.mark-paid', $run) }}" method="POST">
                                        @csrf
                                        <button type="submit"
                                                class="px-2.5 py-1 rounded-lg text-xs font-mono font-medium text-emerald-600 bg-emerald-50 hover:bg-emerald-100 transition-colors">
                                            Mark Paid
                                        </button>
                                    </form>
                                    @endif
                                @endif
                                @if($run->status->value === 'draft')
                                    @if($canDeletePayroll)
                                    <button @click="$dispatch('confirm:delete', { action: '{{ route('payroll.destroy', $run) }}' })"
                                            class="p-1.5 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                    @endif
                                @endif
                            </div>
                        </td>
                    </tr>
                    {{-- Expanded employee breakdown --}}
                    @if($run->items->isNotEmpty())
                        <tr class="bg-stone-50/40">
                            <td colspan="6" class="px-5 pb-3 pt-0">
                                <div class="mt-1 rounded-lg border border-slate-100 overflow-hidden">
                                    <div class="overflow-x-auto">
                                    <table class="w-full text-xs">
                                        <thead class="bg-slate-50">
                                            <tr>
                                                <th class="text-left px-4 py-2 font-mono font-medium text-slate-400 uppercase tracking-wider">Employee</th>
                                                <th class="text-right px-4 py-2 font-mono font-medium text-slate-400 uppercase tracking-wider">Base</th>
                                                <th class="text-right px-4 py-2 font-mono font-medium text-slate-400 uppercase tracking-wider">Bonuses</th>
                                                <th class="text-right px-4 py-2 font-mono font-medium text-slate-400 uppercase tracking-wider">Deductions</th>
                                                <th class="text-right px-4 py-2 font-mono font-medium text-slate-400 uppercase tracking-wider">Net</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100">
                                            @foreach($run->items as $item)
                                                <tr>
                                                    <td class="px-4 py-2 text-slate-700 font-medium">{{ $item->employee->user?->name ?? '—' }}</td>
                                                    <td class="px-4 py-2 text-right font-mono text-slate-600">${{ number_format($item->base_salary, 2) }}</td>
                                                    <td class="px-4 py-2 text-right font-mono text-emerald-600">+${{ number_format($item->bonuses, 2) }}</td>
                                                    <td class="px-4 py-2 text-right font-mono text-red-500">-${{ number_format($item->deductions, 2) }}</td>
                                                    <td class="px-4 py-2 text-right font-mono font-semibold text-slate-800">${{ number_format($item->net_salary, 2) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endif
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-14 text-center">
                            <p class="text-slate-400 font-mono text-sm">No payroll runs yet.</p>
                            @if(auth()->user()->hasPermission('create_payroll'))
                            <button @click="$dispatch('panel:create')" class="mt-3 text-sm text-[#E26B3D] hover:underline font-mono">Create the first run</button>
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
        @if($runs->hasPages())
            <div class="px-5 py-3 border-t border-slate-100">
                {{ $runs->links() }}
            </div>
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
         class="fixed right-0 top-0 h-full w-full max-w-2xl bg-white shadow-2xl z-50 flex flex-col" style="display:none;">

        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 shrink-0">
            <h2 class="text-base font-semibold text-slate-800">New Payroll Run</h2>
            <button @click="close()" class="p-1.5 rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        @if($errors->any())
            <div class="mx-6 mt-4 p-3 rounded-lg bg-red-50 border border-red-200">
                <ul class="text-xs text-red-600 space-y-0.5">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('payroll.store') }}" @submit="submitted = true" class="flex-1 overflow-y-auto flex flex-col">
            @csrf
            <div class="px-6 py-6 space-y-6 flex-1">

                {{-- Period --}}
                <div>
                    <h3 class="text-xs font-mono font-semibold text-slate-400 uppercase tracking-widest mb-4">Pay Period</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1.5">Period Start <span class="text-red-500">*</span></label>
                            <input type="date" name="period_start" :value="periodStart" @change="periodStart = $event.target.value"
                                   class="w-full text-sm border rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#E26B3D]/40 focus:border-[#E26B3D] text-slate-700 transition-colors"
                                   :class="submitted && !periodStart ? 'border-red-400' : 'border-slate-300'">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1.5">Period End <span class="text-red-500">*</span></label>
                            <input type="date" name="period_end" :value="periodEnd" @change="periodEnd = $event.target.value"
                                   class="w-full text-sm border rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#E26B3D]/40 focus:border-[#E26B3D] text-slate-700 transition-colors"
                                   :class="submitted && !periodEnd ? 'border-red-400' : 'border-slate-300'">
                        </div>
                    </div>
                    <div class="mt-4">
                        <label class="block text-xs font-medium text-slate-600 mb-1.5">Notes <span class="text-slate-400 font-normal">(optional)</span></label>
                        <textarea name="notes" rows="2" x-effect="$el.value = notes"
                                  placeholder="Any notes for this payroll run..."
                                  class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#E26B3D]/40 focus:border-[#E26B3D] text-slate-700 placeholder-slate-400 resize-none transition-colors"></textarea>
                    </div>
                </div>

                <div class="border-t border-slate-100"></div>

                {{-- Employee Items --}}
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xs font-mono font-semibold text-slate-400 uppercase tracking-widest">Employees</h3>
                        <button type="button" @click="addItem()"
                                class="inline-flex items-center gap-1 text-xs font-mono text-[#E26B3D] hover:text-[#c8602a] transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Add Employee
                        </button>
                    </div>

                    <div class="space-y-4">
                        <template x-for="(item, index) in items" :key="index">
                            <div class="p-4 rounded-xl border border-slate-200 bg-stone-50/50 relative">
                                <button type="button" @click="removeItem(index)"
                                        x-show="items.length > 1"
                                        class="absolute top-3 right-3 p-1 rounded text-slate-300 hover:text-red-500 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                                <div class="grid grid-cols-2 gap-3 mb-3">
                                    <div class="col-span-2">
                                        <label class="block text-xs font-medium text-slate-600 mb-1">Employee <span class="text-red-500">*</span></label>
                                        <select :name="'items[' + index + '][employee_id]'"
                                                x-effect="$el.value = item.employee_id"
                                                @change="item.employee_id = $event.target.value"
                                                class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#E26B3D]/40 focus:border-[#E26B3D] text-slate-700 bg-white transition-colors">
                                            <option value="">Select employee...</option>
                                            @foreach($employees as $emp)
                                                <option value="{{ $emp->id }}">{{ $emp->user?->name ?? 'Employee #'.$emp->id }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-slate-600 mb-1">Base Salary <span class="text-red-500">*</span></label>
                                        <div class="relative">
                                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs font-mono">$</span>
                                            <input type="number" :name="'items[' + index + '][base_salary]'"
                                                   :value="item.base_salary" @input="item.base_salary = $event.target.value"
                                                   step="0.01" min="0" placeholder="0.00"
                                                   class="w-full pl-6 pr-3 py-2 text-sm border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#E26B3D]/40 focus:border-[#E26B3D] text-slate-700 font-mono transition-colors">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-slate-600 mb-1">Bonuses</label>
                                        <div class="relative">
                                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs font-mono">$</span>
                                            <input type="number" :name="'items[' + index + '][bonuses]'"
                                                   :value="item.bonuses" @input="item.bonuses = $event.target.value"
                                                   step="0.01" min="0" placeholder="0.00"
                                                   class="w-full pl-6 pr-3 py-2 text-sm border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#E26B3D]/40 focus:border-[#E26B3D] text-slate-700 font-mono transition-colors">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-slate-600 mb-1">Deductions</label>
                                        <div class="relative">
                                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs font-mono">$</span>
                                            <input type="number" :name="'items[' + index + '][deductions]'"
                                                   :value="item.deductions" @input="item.deductions = $event.target.value"
                                                   step="0.01" min="0" placeholder="0.00"
                                                   class="w-full pl-6 pr-3 py-2 text-sm border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#E26B3D]/40 focus:border-[#E26B3D] text-slate-700 font-mono transition-colors">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-slate-600 mb-1">Net Salary</label>
                                        <div class="px-3 py-2 text-sm border border-slate-200 rounded-lg bg-white font-mono font-semibold text-slate-800">
                                            $<span x-text="((parseFloat(item.base_salary)||0) + (parseFloat(item.bonuses)||0) - (parseFloat(item.deductions)||0)).toFixed(2)"></span>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-slate-600 mb-1">Notes <span class="text-slate-400 font-normal">(optional)</span></label>
                                    <input type="text" :name="'items[' + index + '][notes]'"
                                           :value="item.notes" @input="item.notes = $event.target.value"
                                           placeholder="Any notes..."
                                           class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#E26B3D]/40 focus:border-[#E26B3D] text-slate-700 transition-colors">
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

            </div>

            <div class="px-6 py-4 border-t border-slate-100 shrink-0 flex gap-3">
                <button type="submit"
                        class="flex-1 bg-[#E26B3D] hover:bg-[#c8602a] text-white text-sm font-medium py-2.5 rounded-lg transition-colors font-mono">
                    Create Payroll Run
                </button>
                <button type="button" @click="close()"
                        class="px-5 py-2.5 text-sm font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors font-mono">
                    Cancel
                </button>
            </div>
        </form>
    </div>

</div>
@endsection

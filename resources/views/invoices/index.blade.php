@extends('layouts.app')

@section('title', 'Invoices')
@section('page-title', 'Invoices')

@section('header-actions')
    @if(auth()->user()->hasPermission('create_invoices'))
    <button @click="$dispatch('panel:create')"
            class="inline-flex items-center gap-2 rounded-lg bg-[#E26B3D] px-4 py-2 text-sm font-mono font-medium text-white hover:bg-[#c8602a] transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        New Invoice
    </button>
    @endif
@endsection

@section('content')
@php $defaultItems = old('items', [['description' => '', 'quantity' => '1', 'unit_price' => '']]); @endphp
<div x-data="{
    open: {{ $errors->any() ? 'true' : 'false' }},
    mode: '{{ old('_mode', 'create') }}',
    paymentOpen: false,
    paymentInvoiceId: null,
    recordId: {{ old('record_id', 'null') }},
    submitted: false,
    formData: {
        title:       '{{ old('title', '') }}',
        client_id:   '{{ old('client_id', '') }}',
        project_id:  '{{ old('project_id', '') }}',
        issue_date:  '{{ old('issue_date', '') }}',
        due_date:    '{{ old('due_date', '') }}',
        status:      '{{ old('status', 'draft') }}',
        tax_rate:    '{{ old('tax_rate', '0') }}',
        currency:    '{{ old('currency', 'USD') }}',
        description: '{{ old('description', '') }}',
        notes:       '{{ old('notes', '') }}'
    },
    items: {{ json_encode($defaultItems) }},
    openCreate() {
        this.mode = 'create';
        this.recordId = null;
        this.submitted = false;
        this.formData = { title:'', client_id:'', project_id:'', issue_date:'', due_date:'', status:'draft', tax_rate:'0', currency:'USD', description:'', notes:'' };
        this.items = [{ description:'', quantity:'1', unit_price:'' }];
        this.open = true;
    },
    openEdit(data) {
        this.mode = 'edit';
        this.recordId = data.id;
        this.submitted = false;
        this.formData = data;
        this.open = true;
    },
    openPayment(invoiceId) {
        this.paymentInvoiceId = invoiceId;
        this.paymentOpen = true;
    },
    addItem() { this.items.push({ description:'', quantity:'1', unit_price:'' }); },
    removeItem(i) { this.items.splice(i, 1); },
    subtotal() {
        return this.items.reduce((s, item) => s + (parseFloat(item.quantity)||0) * (parseFloat(item.unit_price)||0), 0);
    },
    tax() { return this.subtotal() * (parseFloat(this.formData.tax_rate)||0) / 100; },
    total() { return this.subtotal() + this.tax(); },
    close() { this.open = false; this.submitted = false; }
}" @panel:create.window="openCreate()">

    {{-- Filters --}}
    <div class="mb-5">
        <form method="GET" action="{{ route('invoices.index') }}" class="flex flex-wrap items-center gap-3">
            <div class="relative flex-1 min-w-[200px] max-w-sm">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search by number or title..."
                       class="w-full pl-9 pr-4 py-2 rounded-lg border border-slate-300 bg-white text-sm text-slate-800 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-[#E26B3D] font-mono">
            </div>
            <select name="client_id" class="py-2 pl-3 pr-8 rounded-lg border border-slate-300 bg-white text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#E26B3D] font-mono">
                <option value="">All Clients</option>
                @foreach($clients as $client)
                    <option value="{{ $client->id }}" {{ request('client_id') == $client->id ? 'selected' : '' }}>{{ $client->company_name }}</option>
                @endforeach
            </select>
            <select name="status" class="py-2 pl-3 pr-8 rounded-lg border border-slate-300 bg-white text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#E26B3D] font-mono">
                <option value="">All Statuses</option>
                @foreach($statuses as $s)
                    <option value="{{ $s->value }}" {{ request('status') === $s->value ? 'selected' : '' }}>{{ ucfirst($s->value) }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 rounded-lg bg-white border border-slate-300 text-sm text-slate-700 hover:bg-stone-50 transition-colors font-mono">Search</button>
            @if(request()->hasAny(['search', 'client_id', 'status']))
                <a href="{{ route('invoices.index') }}" class="text-sm text-slate-500 hover:text-slate-700 font-mono">Clear</a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-stone-50 border-b border-slate-200">
                <tr>
                    <th class="text-left px-5 py-3.5 text-xs font-mono font-medium text-slate-500 uppercase tracking-wider">Invoice</th>
                    <th class="text-left px-5 py-3.5 text-xs font-mono font-medium text-slate-500 uppercase tracking-wider">Client</th>
                    <th class="text-left px-5 py-3.5 text-xs font-mono font-medium text-slate-500 uppercase tracking-wider">Project</th>
                    <th class="text-left px-5 py-3.5 text-xs font-mono font-medium text-slate-500 uppercase tracking-wider">Total</th>
                    <th class="text-left px-5 py-3.5 text-xs font-mono font-medium text-slate-500 uppercase tracking-wider">Due Date</th>
                    <th class="text-left px-5 py-3.5 text-xs font-mono font-medium text-slate-500 uppercase tracking-wider">Status</th>
                    <th class="text-right px-5 py-3.5 text-xs font-mono font-medium text-slate-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @php
                    $canEditInv   = auth()->user()->hasPermission('edit_invoices');
                    $canDeleteInv = auth()->user()->hasPermission('delete_invoices');
                    $canPayInv    = auth()->user()->hasPermission('record_invoice_payment');
                @endphp
                @forelse($invoices as $invoice)
                    @php
                        $statusColors = [
                            'draft'     => 'bg-slate-100 text-slate-600',
                            'sent'      => 'bg-blue-100 text-blue-700',
                            'paid'      => 'bg-emerald-100 text-emerald-700',
                            'overdue'   => 'bg-red-100 text-red-700',
                            'cancelled' => 'bg-slate-100 text-slate-400',
                        ];
                        $sc = $statusColors[$invoice->status->value] ?? 'bg-slate-100 text-slate-600';
                        $totalPaid = $invoice->payments->sum('amount');
                        $remaining = max(0, $invoice->total - $totalPaid);
                    @endphp
                    <tr class="hover:bg-stone-50/60 transition-colors">
                        <td class="px-5 py-4">
                            <p class="font-mono font-semibold text-slate-800 text-xs">{{ $invoice->invoice_number }}</p>
                            <p class="text-slate-600 mt-0.5">{{ $invoice->title }}</p>
                        </td>
                        <td class="px-5 py-4 text-slate-700">{{ $invoice->client?->company_name ?? '—' }}</td>
                        <td class="px-5 py-4">
                            @if($invoice->project)
                                <a href="{{ route('projects.show', $invoice->project) }}" class="text-[#E26B3D] hover:underline font-mono text-xs">{{ $invoice->project->name }}</a>
                            @else
                                <span class="text-slate-400 font-mono text-xs">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            <p class="font-mono font-semibold text-slate-800">${{ number_format($invoice->total, 2) }}</p>
                            @if($remaining > 0 && $invoice->status->value !== 'paid')
                                <p class="text-xs font-mono text-slate-400 mt-0.5">${{ number_format($remaining, 2) }} remaining</p>
                            @endif
                        </td>
                        <td class="px-5 py-4 font-mono text-xs text-slate-600">{{ $invoice->due_date->format('M d, Y') }}</td>
                        <td class="px-5 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-mono font-medium {{ $sc }}">
                                {{ ucfirst($invoice->status->value) }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-right">
                            <div class="inline-flex items-center gap-1.5">
                                @if($canPayInv && !in_array($invoice->status->value, ['paid','cancelled']))
                                    <button @click="openPayment({{ $invoice->id }})"
                                            class="p-1.5 rounded-lg text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 transition-colors" title="Record Payment">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                        </svg>
                                    </button>
                                @endif
                                @if($canEditInv)
                                <button @click="openEdit({
                                            id:          {{ $invoice->id }},
                                            title:       '{{ e($invoice->title) }}',
                                            client_id:   '{{ $invoice->client_id }}',
                                            project_id:  '{{ $invoice->project_id ?? '' }}',
                                            issue_date:  '{{ $invoice->issue_date->format('Y-m-d') }}',
                                            due_date:    '{{ $invoice->due_date->format('Y-m-d') }}',
                                            status:      '{{ $invoice->status->value }}',
                                            tax_rate:    '{{ $invoice->tax_rate }}',
                                            currency:    '{{ $invoice->currency }}',
                                            description: `{{ e($invoice->description ?? '') }}`,
                                            notes:       `{{ e($invoice->notes ?? '') }}`
                                        })"
                                        class="p-1.5 rounded-lg text-slate-400 hover:text-[#E26B3D] hover:bg-[#E26B3D]/10 transition-colors" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </button>
                                @endif
                                @if($canDeleteInv)
                                <button @click="$dispatch('confirm:delete', { action: '{{ route('invoices.destroy', $invoice) }}' })"
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
                            <p class="text-slate-400 font-mono text-sm">No invoices yet.</p>
                            @if(auth()->user()->hasPermission('create_invoices'))
                            <button @click="$dispatch('panel:create')" class="mt-3 text-sm text-[#E26B3D] hover:underline font-mono">Create the first invoice</button>
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
        @if($invoices->hasPages())
            <div class="px-5 py-3 border-t border-slate-100">{{ $invoices->links() }}</div>
        @endif
    </div>

    {{-- Backdrop --}}
    <div x-show="open || paymentOpen" @click="open = false; paymentOpen = false;"
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-black/40 z-40" style="display:none;"></div>

    {{-- Create / Edit Slide-over --}}
    <div x-show="open"
         x-transition:enter="transition ease-out duration-300" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
         x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
         class="fixed right-0 top-0 h-full w-full max-w-2xl bg-white shadow-2xl z-50 flex flex-col" style="display:none;">

        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 shrink-0">
            <h2 class="text-base font-semibold text-slate-800"
                x-text="mode === 'create' ? 'New Invoice' : 'Edit Invoice'"></h2>
            <button @click="close()" class="p-1.5 rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
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
              :action="mode === 'create' ? '{{ route('invoices.store') }}' : '{{ url('invoices') }}/' + recordId"
              @submit="submitted = true" class="flex-1 overflow-y-auto flex flex-col">
            @csrf
            <input type="hidden" name="_mode"     :value="mode">
            <input type="hidden" name="record_id" :value="recordId">

            <div class="px-6 py-6 space-y-6 flex-1">

                {{-- Details --}}
                <div>
                    <h3 class="text-xs font-mono font-semibold text-slate-400 uppercase tracking-widest mb-4">Invoice Details</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1.5">Title <span class="text-red-500">*</span></label>
                            <input type="text" name="title" :value="formData.title"
                                   placeholder="e.g. Website Development — Phase 1"
                                   class="w-full text-sm border rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#E26B3D]/40 focus:border-[#E26B3D] text-slate-700 transition-colors"
                                   :class="submitted && !formData.title ? 'border-red-400' : 'border-slate-300'">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1.5">Client <span class="text-red-500">*</span></label>
                                <select name="client_id"
                                        x-effect="$el.value = formData.client_id"
                                        @change="formData.client_id = $event.target.value"
                                        class="w-full text-sm border rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#E26B3D]/40 focus:border-[#E26B3D] text-slate-700 bg-white transition-colors"
                                        :class="submitted && !formData.client_id ? 'border-red-400' : 'border-slate-300'">
                                    <option value="">Select client...</option>
                                    @foreach($clients as $client)
                                        <option value="{{ $client->id }}">{{ $client->company_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1.5">Project <span class="text-slate-400 font-normal">(optional)</span></label>
                                <select name="project_id"
                                        x-effect="$el.value = formData.project_id"
                                        @change="formData.project_id = $event.target.value"
                                        class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#E26B3D]/40 focus:border-[#E26B3D] text-slate-700 bg-white transition-colors">
                                    <option value="">No project</option>
                                    @foreach($projects as $proj)
                                        <option value="{{ $proj->id }}">{{ $proj->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1.5">Issue Date <span class="text-red-500">*</span></label>
                                <input type="date" name="issue_date" :value="formData.issue_date"
                                       class="w-full text-sm border rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#E26B3D]/40 focus:border-[#E26B3D] text-slate-700 transition-colors"
                                       :class="submitted && !formData.issue_date ? 'border-red-400' : 'border-slate-300'">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1.5">Due Date <span class="text-red-500">*</span></label>
                                <input type="date" name="due_date" :value="formData.due_date"
                                       class="w-full text-sm border rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#E26B3D]/40 focus:border-[#E26B3D] text-slate-700 transition-colors"
                                       :class="submitted && !formData.due_date ? 'border-red-400' : 'border-slate-300'">
                            </div>
                        </div>
                        <div x-show="mode === 'edit'">
                            <label class="block text-xs font-medium text-slate-600 mb-1.5">Status</label>
                            <select name="status"
                                    x-effect="$el.value = formData.status"
                                    @change="formData.status = $event.target.value"
                                    class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#E26B3D]/40 focus:border-[#E26B3D] text-slate-700 bg-white transition-colors">
                                @foreach($statuses as $s)
                                    <option value="{{ $s->value }}">{{ ucfirst($s->value) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="border-t border-slate-100" x-show="mode === 'create'"></div>

                {{-- Line Items (create only) --}}
                <div x-show="mode === 'create'">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xs font-mono font-semibold text-slate-400 uppercase tracking-widest">Line Items</h3>
                        <button type="button" @click="addItem()"
                                class="inline-flex items-center gap-1 text-xs font-mono text-[#E26B3D] hover:text-[#c8602a] transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Add Line
                        </button>
                    </div>
                    <div class="space-y-3">
                        <template x-for="(item, index) in items" :key="index">
                            <div class="flex items-start gap-2">
                                <div class="flex-1 grid grid-cols-5 gap-2">
                                    <div class="col-span-3">
                                        <input type="text" :name="'items[' + index + '][description]'"
                                               :value="item.description" @input="item.description = $event.target.value"
                                               placeholder="Description"
                                               class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#E26B3D]/40 focus:border-[#E26B3D] text-slate-700 transition-colors">
                                    </div>
                                    <div>
                                        <input type="number" :name="'items[' + index + '][quantity]'"
                                               :value="item.quantity" @input="item.quantity = $event.target.value"
                                               placeholder="Qty" step="0.01" min="0.01"
                                               class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#E26B3D]/40 focus:border-[#E26B3D] text-slate-700 font-mono transition-colors">
                                    </div>
                                    <div class="relative">
                                        <span class="absolute left-2 top-1/2 -translate-y-1/2 text-slate-400 text-xs font-mono">$</span>
                                        <input type="number" :name="'items[' + index + '][unit_price]'"
                                               :value="item.unit_price" @input="item.unit_price = $event.target.value"
                                               placeholder="Price" step="0.01" min="0"
                                               class="w-full pl-5 pr-2 py-2 text-sm border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#E26B3D]/40 focus:border-[#E26B3D] text-slate-700 font-mono transition-colors">
                                    </div>
                                </div>
                                <div class="w-20 px-2 py-2 text-sm font-mono font-medium text-slate-700 text-right whitespace-nowrap pt-2.5">
                                    $<span x-text="((parseFloat(item.quantity)||0)*(parseFloat(item.unit_price)||0)).toFixed(2)"></span>
                                </div>
                                <button type="button" @click="removeItem(index)" x-show="items.length > 1"
                                        class="p-2 text-slate-300 hover:text-red-500 transition-colors mt-0.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        </template>
                    </div>

                    {{-- Totals --}}
                    <div class="mt-4 rounded-xl bg-stone-50 border border-slate-200 p-4 space-y-1.5">
                        <div class="flex justify-between text-sm text-slate-600">
                            <span class="font-mono">Subtotal</span>
                            <span class="font-mono font-medium" x-text="'$' + subtotal().toFixed(2)"></span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="font-mono text-sm text-slate-600">Tax</span>
                            <div class="relative">
                                <input type="number" name="tax_rate" step="0.01" min="0" max="100"
                                       :value="formData.tax_rate" @input="formData.tax_rate = $event.target.value"
                                       class="w-20 pr-5 py-1 text-sm border border-slate-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-[#E26B3D] text-slate-700 font-mono text-right transition-colors">
                                <span class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 text-xs">%</span>
                            </div>
                            <span class="font-mono text-sm text-slate-600 ml-auto" x-text="'$' + tax().toFixed(2)"></span>
                        </div>
                        <div class="flex justify-between text-base font-semibold text-slate-800 border-t border-slate-200 pt-1.5 mt-1">
                            <span class="font-mono">Total</span>
                            <span class="font-mono" x-text="'$' + total().toFixed(2)"></span>
                        </div>
                    </div>
                </div>

                <div class="border-t border-slate-100"></div>

                {{-- Notes --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1.5">Currency</label>
                        <input type="text" name="currency" :value="formData.currency" maxlength="3"
                               class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#E26B3D]/40 focus:border-[#E26B3D] text-slate-700 font-mono uppercase transition-colors">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1.5">Notes <span class="text-slate-400 font-normal">(optional)</span></label>
                    <textarea name="notes" rows="2" x-effect="$el.value = formData.notes"
                              placeholder="Payment instructions, terms..."
                              class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#E26B3D]/40 focus:border-[#E26B3D] text-slate-700 placeholder-slate-400 resize-none transition-colors"></textarea>
                </div>
            </div>

            <div class="px-6 py-4 border-t border-slate-100 shrink-0 flex gap-3">
                <button type="submit"
                        class="flex-1 bg-[#E26B3D] hover:bg-[#c8602a] text-white text-sm font-medium py-2.5 rounded-lg transition-colors font-mono"
                        x-text="mode === 'create' ? 'Create Invoice' : 'Save Changes'"></button>
                <button type="button" @click="close()"
                        class="px-5 py-2.5 text-sm font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors font-mono">
                    Cancel
                </button>
            </div>
        </form>
    </div>

    {{-- Record Payment Modal --}}
    <div x-show="paymentOpen"
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
         class="fixed inset-0 z-50 flex items-center justify-center p-6" style="display:none;" @click.self="paymentOpen = false">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md border border-slate-200 overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200">
                <h3 class="text-base font-semibold text-slate-800">Record Payment</h3>
                <button @click="paymentOpen = false" class="p-1.5 rounded-lg text-slate-400 hover:bg-slate-100 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <form :action="'/invoices/' + paymentInvoiceId + '/payment'" method="POST" class="px-6 py-5 space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1.5">Amount <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm font-mono">$</span>
                            <input type="number" name="amount" step="0.01" min="0.01" required
                                   class="w-full pl-7 pr-3 py-2.5 text-sm border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#E26B3D]/40 focus:border-[#E26B3D] text-slate-700 font-mono transition-colors">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1.5">Date <span class="text-red-500">*</span></label>
                        <input type="date" name="payment_date" required
                               class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#E26B3D]/40 focus:border-[#E26B3D] text-slate-700 transition-colors">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1.5">Method <span class="text-red-500">*</span></label>
                    <select name="method" required
                            class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#E26B3D]/40 focus:border-[#E26B3D] text-slate-700 bg-white transition-colors">
                        @foreach($paymentMethods as $m)
                            <option value="{{ $m->value }}">{{ ucfirst($m->value) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1.5">Reference <span class="text-slate-400 font-normal">(optional)</span></label>
                    <input type="text" name="reference" placeholder="Transaction ID, cheque no..."
                           class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#E26B3D]/40 focus:border-[#E26B3D] text-slate-700 transition-colors">
                </div>
                <div class="flex gap-3 pt-1">
                    @if($canPayInv)
                    <button type="submit"
                            class="flex-1 bg-[#E26B3D] hover:bg-[#c8602a] text-white text-sm font-medium py-2.5 rounded-lg transition-colors font-mono">
                        Record Payment
                    </button>
                    @endif
                    <button type="button" @click="paymentOpen = false"
                            class="px-5 py-2.5 text-sm font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors font-mono">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

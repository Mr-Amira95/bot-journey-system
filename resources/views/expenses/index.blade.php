@extends('layouts.app')

@section('title', 'Expenses')
@section('page-title', 'Expenses')

@section('header-actions')
    <a href="{{ route('expense-categories.index') }}"
       class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-mono font-medium text-slate-600 hover:bg-stone-50 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
        </svg>
        Categories
    </a>
    <a href="{{ route('reports.expenses') . '?' . http_build_query(request()->only('from','to','employee_id')) }}"
       target="_blank"
       class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-mono font-medium text-slate-600 hover:bg-stone-50 transition-colors"
       title="Export PDF">
        <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
        </svg>
        PDF
    </a>
    <a href="{{ route('reports.expenses-excel') . '?' . http_build_query(request()->only('from','to','employee_id')) }}"
       class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-mono font-medium text-slate-600 hover:bg-stone-50 transition-colors"
       title="Export Excel">
        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
        </svg>
        Excel
    </a>
    @if(auth()->user()->hasPermission('create_expenses'))
    <button @click="$dispatch('panel:create')"
            class="inline-flex items-center gap-2 rounded-lg bg-[#E26B3D] px-4 py-2 text-sm font-mono font-medium text-white hover:bg-[#c8602a] transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        New Expense
    </button>
    @endif
@endsection

@section('content')
@if($viewingAll)
<div class="flex items-center gap-1 bg-slate-100 rounded-xl p-1 w-fit mb-5">
    <a href="{{ route('expenses.index', ['tab' => 'mine']) }}"
       class="px-4 py-2 rounded-lg text-sm font-medium transition-all {{ $tab === 'mine' ? 'bg-white shadow text-slate-800' : 'text-slate-500 hover:text-slate-700' }}">
        My Expenses
    </a>
    <a href="{{ route('expenses.index', ['tab' => 'all']) }}"
       class="px-4 py-2 rounded-lg text-sm font-medium transition-all flex items-center gap-1.5 {{ $tab === 'all' ? 'bg-white shadow text-slate-800' : 'text-slate-500 hover:text-slate-700' }}">
        <svg class="w-3.5 h-3.5 {{ $tab === 'all' ? 'text-[#E26B3D]' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
        </svg>
        All Expenses
    </a>
</div>
@endif
<div x-data="{
    open: {{ $errors->any() ? 'true' : 'false' }},
    mode: '{{ old('_mode', 'create') }}',
    recordId: {{ old('record_id', 'null') }},
    submitted: false,
    formData: {
        title:        '{{ old('title', '') }}',
        description:  '{{ old('description', '') }}',
        category_id:  '{{ old('category_id', '') }}',
        project_id:   '{{ old('project_id', '') }}',
        paid_by:      '{{ old('paid_by', '') }}',
        amount:       '{{ old('amount', '') }}',
        expense_date: '{{ old('expense_date', '') }}',
        status:       '{{ old('status', 'pending') }}'
    },
    openCreate() {
        this.mode = 'create';
        this.recordId = null;
        this.submitted = false;
        this.formData = { title: '', description: '', category_id: '', project_id: '', paid_by: '', amount: '', expense_date: '', status: 'pending' };
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
        <form method="GET" action="{{ route('expenses.index') }}" class="flex flex-wrap items-center gap-3">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <div class="relative flex-1 min-w-[200px] max-w-sm">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search by title..."
                       class="w-full pl-9 pr-4 py-2 rounded-lg border border-slate-300 bg-white text-sm text-slate-800 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-[#E26B3D] font-mono">
            </div>
            <select name="category_id" class="py-2 pl-3 pr-8 rounded-lg border border-slate-300 bg-white text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#E26B3D] font-mono">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                        {{ $cat->title ?? ucwords(str_replace('_', ' ', $cat->type->value)) }}
                    </option>
                @endforeach
            </select>
            <select name="project_id" class="py-2 pl-3 pr-8 rounded-lg border border-slate-300 bg-white text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#E26B3D] font-mono">
                <option value="">All Projects</option>
                @foreach($projects as $proj)
                    <option value="{{ $proj->id }}" {{ request('project_id') == $proj->id ? 'selected' : '' }}>{{ $proj->name }}</option>
                @endforeach
            </select>
            <select name="status" class="py-2 pl-3 pr-8 rounded-lg border border-slate-300 bg-white text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#E26B3D] font-mono">
                <option value="">All Statuses</option>
                @foreach($statuses as $s)
                    <option value="{{ $s->value }}" {{ request('status') === $s->value ? 'selected' : '' }}>
                        {{ ucfirst($s->value) }}
                    </option>
                @endforeach
            </select>
            @if($tab === 'all')
            <select name="paid_by_user" class="py-2 pl-3 pr-8 rounded-lg border border-slate-300 bg-white text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#E26B3D] font-mono">
                <option value="">Any Payer</option>
                @foreach($users as $u)
                    <option value="{{ $u->id }}" {{ request('paid_by_user') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                @endforeach
            </select>
            @endif
            <button type="submit" class="px-4 py-2 rounded-lg bg-white border border-slate-300 text-sm text-slate-700 hover:bg-stone-50 transition-colors font-mono">Search</button>
            @if(request()->hasAny(['search', 'category_id', 'project_id', 'status', 'paid_by_user']))
                <a href="{{ route('expenses.index', ['tab' => $tab]) }}" class="text-sm text-slate-500 hover:text-slate-700 font-mono">Clear</a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-stone-50 border-b border-slate-200">
                <tr>
                    <th class="text-left px-5 py-3.5 text-xs font-mono font-medium text-slate-500 uppercase tracking-wider">Title</th>
                    <th class="text-left px-5 py-3.5 text-xs font-mono font-medium text-slate-500 uppercase tracking-wider">Category</th>
                    <th class="text-left px-5 py-3.5 text-xs font-mono font-medium text-slate-500 uppercase tracking-wider">Project</th>
                    <th class="text-left px-5 py-3.5 text-xs font-mono font-medium text-slate-500 uppercase tracking-wider">Paid By</th>
                    <th class="text-left px-5 py-3.5 text-xs font-mono font-medium text-slate-500 uppercase tracking-wider">Amount</th>
                    <th class="text-left px-5 py-3.5 text-xs font-mono font-medium text-slate-500 uppercase tracking-wider">Date</th>
                    <th class="text-left px-5 py-3.5 text-xs font-mono font-medium text-slate-500 uppercase tracking-wider">Status</th>
                    <th class="text-right px-5 py-3.5 text-xs font-mono font-medium text-slate-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @php
                    $canEditExp   = auth()->user()->hasPermission('edit_expenses');
                    $canDeleteExp = auth()->user()->hasPermission('delete_expenses');
                @endphp
                @forelse($expenses as $expense)
                    @php
                        $statusColors = [
                            'pending'  => 'bg-amber-100 text-amber-700',
                            'approved' => 'bg-blue-100 text-blue-700',
                            'rejected' => 'bg-red-100 text-red-700',
                            'paid'     => 'bg-emerald-100 text-emerald-700',
                        ];
                        $sc = $statusColors[$expense->status->value] ?? 'bg-slate-100 text-slate-600';
                    @endphp
                    <tr class="hover:bg-stone-50/60 transition-colors">
                        <td class="px-5 py-4">
                            <p class="font-medium text-slate-800">{{ $expense->title }}</p>
                            @if($expense->description)
                                <p class="text-xs text-slate-400 mt-0.5 truncate max-w-[200px]">{{ $expense->description }}</p>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            @if($expense->category)
                                <span class="text-slate-600 font-mono text-xs">
                                    {{ $expense->category->title ?? ucwords(str_replace('_', ' ', $expense->category->type->value)) }}
                                </span>
                            @else
                                <span class="text-slate-400 font-mono text-xs">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            @if($expense->project)
                                <a href="{{ route('projects.show', $expense->project) }}"
                                   class="text-[#E26B3D] hover:underline font-mono text-xs">
                                    {{ $expense->project->name }}
                                </a>
                            @else
                                <span class="text-slate-400 font-mono text-xs">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            @if($expense->paidBy)
                                <p class="text-slate-700 text-xs font-medium">{{ $expense->paidBy->name }}</p>
                            @else
                                <span class="text-slate-400 font-mono text-xs">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 font-mono text-slate-800 font-medium">
                            ${{ number_format($expense->amount, 2) }}
                        </td>
                        <td class="px-5 py-4 font-mono text-xs text-slate-600">
                            {{ $expense->expense_date->format('M d, Y') }}
                        </td>
                        <td class="px-5 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-mono font-medium {{ $sc }}">
                                {{ ucfirst($expense->status->value) }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-right">
                            <div class="inline-flex items-center gap-1.5">
                                @if($canEditExp)
                                <button @click="openEdit({
                                            id:           {{ $expense->id }},
                                            title:        '{{ e($expense->title) }}',
                                            description:  `{{ e($expense->description ?? '') }}`,
                                            category_id:  '{{ $expense->category_id }}',
                                            project_id:   '{{ $expense->project_id ?? '' }}',
                                            paid_by:      '{{ $expense->paid_by }}',
                                            amount:       '{{ $expense->amount }}',
                                            expense_date: '{{ $expense->expense_date->format('Y-m-d') }}',
                                            status:       '{{ $expense->status->value }}'
                                        })"
                                        class="p-1.5 rounded-lg text-slate-400 hover:text-[#E26B3D] hover:bg-[#E26B3D]/10 transition-colors" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </button>
                                @endif
                                @if($canDeleteExp)
                                <button @click="$dispatch('confirm:delete', { action: '{{ route('expenses.destroy', $expense) }}' })"
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
                        <td colspan="8" class="px-5 py-14 text-center">
                            <p class="text-slate-400 font-mono text-sm">No expenses recorded yet.</p>
                            @if(auth()->user()->hasPermission('create_expenses'))
                            <button @click="$dispatch('panel:create')" class="mt-3 text-sm text-[#E26B3D] hover:underline font-mono">Record the first one</button>
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
        @if($expenses->hasPages())
            <div class="px-5 py-3 border-t border-slate-100">
                {{ $expenses->links() }}
            </div>
        @endif
    </div>

    {{-- Backdrop --}}
    <div x-show="open" @click="close()"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-black/40 z-40" style="display:none;"></div>

    {{-- Slide-over --}}
    <div x-show="open"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="translate-x-full"
         x-transition:enter-end="translate-x-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="translate-x-0"
         x-transition:leave-end="translate-x-full"
         class="fixed right-0 top-0 h-full w-full max-w-lg bg-white shadow-2xl z-50 flex flex-col" style="display:none;">

        {{-- Header --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 shrink-0">
            <h2 class="text-base font-semibold text-slate-800"
                x-text="mode === 'create' ? 'New Expense' : 'Edit Expense'"></h2>
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

        {{-- Form --}}
        <form method="POST"
              :action="mode === 'create' ? '{{ route('expenses.store') }}' : '{{ url('expenses') }}/' + recordId"
              @submit="submitted = true"
              class="flex-1 overflow-y-auto flex flex-col">
            @csrf
            <input type="hidden" name="_mode"     :value="mode">
            <input type="hidden" name="record_id" :value="recordId">

            <div class="px-6 py-6 space-y-6 flex-1">

                {{-- Expense Details --}}
                <div>
                    <h3 class="text-xs font-mono font-semibold text-slate-400 uppercase tracking-widest mb-4">Expense Details</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1.5">Title <span class="text-red-500">*</span></label>
                            <input type="text" name="title" :value="formData.title"
                                   placeholder="e.g. AWS hosting invoice"
                                   class="w-full text-sm border rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#E26B3D]/40 focus:border-[#E26B3D] text-slate-700 placeholder-slate-400 transition-colors"
                                   :class="submitted && !formData.title ? 'border-red-400 ring-1 ring-red-400' : 'border-slate-300'">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1.5">Description <span class="text-slate-400 font-normal">(optional)</span></label>
                            <textarea name="description" rows="3"
                                      x-effect="$el.value = formData.description"
                                      placeholder="Additional notes..."
                                      class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#E26B3D]/40 focus:border-[#E26B3D] text-slate-700 placeholder-slate-400 resize-none transition-colors"></textarea>
                        </div>
                    </div>
                </div>

                <div class="border-t border-slate-100"></div>

                {{-- Assignment --}}
                <div>
                    <h3 class="text-xs font-mono font-semibold text-slate-400 uppercase tracking-widest mb-4">Assignment</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1.5">Category <span class="text-red-500">*</span></label>
                            <select name="category_id"
                                    x-effect="$el.value = formData.category_id"
                                    @change="formData.category_id = $event.target.value"
                                    class="w-full text-sm border rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#E26B3D]/40 focus:border-[#E26B3D] text-slate-700 bg-white transition-colors"
                                    :class="submitted && !formData.category_id ? 'border-red-400 ring-1 ring-red-400' : 'border-slate-300'">
                                <option value="">Select a category...</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">
                                        {{ $cat->title ?? ucwords(str_replace('_', ' ', $cat->type->value)) }}
                                    </option>
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
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1.5">Paid By <span class="text-red-500">*</span></label>
                            <select name="paid_by"
                                    x-effect="$el.value = formData.paid_by"
                                    @change="formData.paid_by = $event.target.value"
                                    class="w-full text-sm border rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#E26B3D]/40 focus:border-[#E26B3D] text-slate-700 bg-white transition-colors"
                                    :class="submitted && !formData.paid_by ? 'border-red-400 ring-1 ring-red-400' : 'border-slate-300'">
                                <option value="">Select a person...</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="border-t border-slate-100"></div>

                {{-- Financials --}}
                <div>
                    <h3 class="text-xs font-mono font-semibold text-slate-400 uppercase tracking-widest mb-4">Financials</h3>
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1.5">Amount <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm font-mono">$</span>
                                    <input type="number" name="amount" step="0.01" min="0"
                                           :value="formData.amount"
                                           placeholder="0.00"
                                           class="w-full pl-7 pr-3 py-2.5 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-[#E26B3D]/40 focus:border-[#E26B3D] text-slate-700 placeholder-slate-400 font-mono transition-colors"
                                           :class="submitted && !formData.amount ? 'border-red-400 ring-1 ring-red-400' : 'border-slate-300'">
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1.5">Date <span class="text-red-500">*</span></label>
                                <input type="date" name="expense_date"
                                       :value="formData.expense_date"
                                       class="w-full text-sm border rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#E26B3D]/40 focus:border-[#E26B3D] text-slate-700 transition-colors"
                                       :class="submitted && !formData.expense_date ? 'border-red-400 ring-1 ring-red-400' : 'border-slate-300'">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1.5">Status <span class="text-red-500">*</span></label>
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

            </div>

            <div class="px-6 py-4 border-t border-slate-100 shrink-0 flex gap-3">
                <button type="submit"
                        class="flex-1 bg-[#E26B3D] hover:bg-[#c8602a] text-white text-sm font-medium py-2.5 rounded-lg transition-colors font-mono"
                        x-text="mode === 'create' ? 'Record Expense' : 'Save Changes'"></button>
                <button type="button" @click="close()"
                        class="px-5 py-2.5 text-sm font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors font-mono">
                    Cancel
                </button>
            </div>
        </form>
    </div>

</div>
@endsection

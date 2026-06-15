@extends('layouts.app')

@section('title', 'Project Budgets')
@section('page-title', 'Project Budgets')

@section('header-actions')
    @if(auth()->user()->hasPermission('create_project_budgets'))
    <button @click="$dispatch('panel:create')"
            class="inline-flex items-center gap-2 rounded-lg bg-[#E26B3D] px-4 py-2 text-sm font-mono font-medium text-white hover:bg-[#c8602a] transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Add Budget Line
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
        project_id:      '{{ old('project_id', '') }}',
        category:        '{{ old('category', '') }}',
        budgeted_amount: '{{ old('budgeted_amount', '') }}',
        notes:           '{{ old('notes', '') }}'
    },
    openCreate() {
        this.mode = 'create';
        this.recordId = null;
        this.submitted = false;
        this.formData = { project_id: '', category: '', budgeted_amount: '', notes: '' };
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
        <form method="GET" action="{{ route('project-budgets.index') }}" class="flex flex-wrap items-center gap-3">
            <div class="relative flex-1 min-w-[200px] max-w-sm">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search by category..."
                       class="w-full pl-9 pr-4 py-2 rounded-lg border border-slate-300 bg-white text-sm text-slate-800 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-[#E26B3D] font-mono">
            </div>
            <select name="project_id" class="py-2 pl-3 pr-8 rounded-lg border border-slate-300 bg-white text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#E26B3D] font-mono">
                <option value="">All Projects</option>
                @foreach($projects as $proj)
                    <option value="{{ $proj->id }}" {{ request('project_id') == $proj->id ? 'selected' : '' }}>{{ $proj->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 rounded-lg bg-white border border-slate-300 text-sm text-slate-700 hover:bg-stone-50 transition-colors font-mono">Search</button>
            @if(request()->hasAny(['search', 'project_id']))
                <a href="{{ route('project-budgets.index') }}" class="text-sm text-slate-500 hover:text-slate-700 font-mono">Clear</a>
            @endif
        </form>
    </div>

    {{-- Summary cards if filtered by project --}}
    @if(request('project_id') && $budgetLines->total() > 0)
        @php
            $totalBudgeted = $budgetLines->sum('budgeted_amount');
        @endphp
        <div class="mb-5 p-4 bg-white rounded-xl border border-slate-200 flex items-center gap-6">
            <div>
                <p class="text-xs font-mono text-slate-400 uppercase tracking-wider">Total Budgeted</p>
                <p class="text-xl font-mono font-semibold text-slate-800 mt-0.5">${{ number_format($totalBudgeted, 2) }}</p>
            </div>
            <div>
                <p class="text-xs font-mono text-slate-400 uppercase tracking-wider">Budget Lines</p>
                <p class="text-xl font-mono font-semibold text-slate-800 mt-0.5">{{ $budgetLines->total() }}</p>
            </div>
        </div>
    @endif

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-stone-50 border-b border-slate-200">
                <tr>
                    <th class="text-left px-5 py-3.5 text-xs font-mono font-medium text-slate-500 uppercase tracking-wider">Project</th>
                    <th class="text-left px-5 py-3.5 text-xs font-mono font-medium text-slate-500 uppercase tracking-wider">Category</th>
                    <th class="text-left px-5 py-3.5 text-xs font-mono font-medium text-slate-500 uppercase tracking-wider">Budgeted Amount</th>
                    <th class="text-left px-5 py-3.5 text-xs font-mono font-medium text-slate-500 uppercase tracking-wider">Notes</th>
                    <th class="text-right px-5 py-3.5 text-xs font-mono font-medium text-slate-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @php
                    $canEditPB   = auth()->user()->hasPermission('edit_project_budgets');
                    $canDeletePB = auth()->user()->hasPermission('delete_project_budgets');
                @endphp
                @forelse($budgetLines as $line)
                    <tr class="hover:bg-stone-50/60 transition-colors">
                        <td class="px-5 py-4">
                            <a href="{{ route('projects.show', $line->project) }}" class="text-[#E26B3D] hover:underline font-medium text-sm">
                                {{ $line->project->name }}
                            </a>
                        </td>
                        <td class="px-5 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-mono font-medium bg-blue-50 text-blue-700">
                                {{ $line->category }}
                            </span>
                        </td>
                        <td class="px-5 py-4 font-mono font-semibold text-slate-800">
                            ${{ number_format($line->budgeted_amount, 2) }}
                        </td>
                        <td class="px-5 py-4 text-sm text-slate-500 max-w-[200px] truncate">
                            {{ $line->notes ?? '—' }}
                        </td>
                        <td class="px-5 py-4 text-right">
                            <div class="inline-flex items-center gap-1.5">
                                @if($canEditPB)
                                <button @click="openEdit({
                                            id:              {{ $line->id }},
                                            project_id:      '{{ $line->project_id }}',
                                            category:        '{{ e($line->category) }}',
                                            budgeted_amount: '{{ $line->budgeted_amount }}',
                                            notes:           `{{ e($line->notes ?? '') }}`
                                        })"
                                        class="p-1.5 rounded-lg text-slate-400 hover:text-[#E26B3D] hover:bg-[#E26B3D]/10 transition-colors" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </button>
                                @endif
                                @if($canDeletePB)
                                <button @click="$dispatch('confirm:delete', { action: '{{ route('project-budgets.destroy', $line) }}' })"
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
                            <p class="text-slate-400 font-mono text-sm">No budget lines yet.</p>
                            @if(auth()->user()->hasPermission('create_project_budgets'))
                            <button @click="$dispatch('panel:create')" class="mt-3 text-sm text-[#E26B3D] hover:underline font-mono">Add the first budget line</button>
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
        @if($budgetLines->hasPages())
            <div class="px-5 py-3 border-t border-slate-100">{{ $budgetLines->links() }}</div>
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
            <h2 class="text-base font-semibold text-slate-800"
                x-text="mode === 'create' ? 'Add Budget Line' : 'Edit Budget Line'"></h2>
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
              :action="mode === 'create' ? '{{ route('project-budgets.store') }}' : '{{ url('project-budgets') }}/' + recordId"
              @submit="submitted = true" class="flex-1 overflow-y-auto flex flex-col">
            @csrf
            <input type="hidden" name="_mode"     :value="mode">
            <input type="hidden" name="record_id" :value="recordId">

            <div class="px-6 py-6 space-y-5 flex-1">

                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1.5">Project <span class="text-red-500">*</span></label>
                    <select name="project_id"
                            x-effect="$el.value = formData.project_id"
                            @change="formData.project_id = $event.target.value"
                            class="w-full text-sm border rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#E26B3D]/40 focus:border-[#E26B3D] text-slate-700 bg-white transition-colors"
                            :class="submitted && !formData.project_id ? 'border-red-400' : 'border-slate-300'">
                        <option value="">Select project...</option>
                        @foreach($projects as $proj)
                            <option value="{{ $proj->id }}">{{ $proj->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1.5">Category <span class="text-red-500">*</span></label>
                    <input type="text" name="category" :value="formData.category"
                           placeholder="e.g. Development, Design, Marketing, Salaries..."
                           class="w-full text-sm border rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#E26B3D]/40 focus:border-[#E26B3D] text-slate-700 transition-colors"
                           :class="submitted && !formData.category ? 'border-red-400' : 'border-slate-300'">
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1.5">Budgeted Amount <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm font-mono">$</span>
                        <input type="number" name="budgeted_amount" step="0.01" min="0" :value="formData.budgeted_amount"
                               placeholder="0.00"
                               class="w-full pl-7 pr-3 py-2.5 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-[#E26B3D]/40 focus:border-[#E26B3D] text-slate-700 font-mono transition-colors"
                               :class="submitted && !formData.budgeted_amount ? 'border-red-400' : 'border-slate-300'">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1.5">Notes <span class="text-slate-400 font-normal">(optional)</span></label>
                    <textarea name="notes" rows="3" x-effect="$el.value = formData.notes"
                              placeholder="Additional context..."
                              class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#E26B3D]/40 focus:border-[#E26B3D] text-slate-700 placeholder-slate-400 resize-none transition-colors"></textarea>
                </div>
            </div>

            <div class="px-6 py-4 border-t border-slate-100 shrink-0 flex gap-3">
                <button type="submit"
                        class="flex-1 bg-[#E26B3D] hover:bg-[#c8602a] text-white text-sm font-medium py-2.5 rounded-lg transition-colors font-mono"
                        x-text="mode === 'create' ? 'Add Budget Line' : 'Save Changes'"></button>
                <button type="button" @click="close()"
                        class="px-5 py-2.5 text-sm font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors font-mono">
                    Cancel
                </button>
            </div>
        </form>
    </div>

</div>
@endsection

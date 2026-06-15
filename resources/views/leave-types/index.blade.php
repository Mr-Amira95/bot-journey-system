@extends('layouts.app')

@section('title', 'Leave Types')
@section('page-title', 'Leave Types')

@section('header-actions')
    @if(auth()->user()->hasPermission('create_leave_types'))
    <button @click="$dispatch('panel:create')"
            class="inline-flex items-center gap-2 rounded-lg bg-[#E26B3D] px-4 py-2 text-sm font-mono font-medium text-white hover:bg-[#c8602a] transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        New Leave Type
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
        name:              '{{ old('name', '') }}',
        is_paid:           {{ old('is_paid', '1') == '1' ? 'true' : 'false' }},
        max_days_per_year: '{{ old('max_days_per_year', '') }}',
        requires_approval: {{ old('requires_approval', '1') == '1' ? 'true' : 'false' }},
        color:             '{{ old('color', '#6366f1') }}'
    },
    openCreate() {
        this.mode = 'create'; this.recordId = null; this.submitted = false;
        this.formData = { name: '', is_paid: true, max_days_per_year: '', requires_approval: true, color: '#6366f1' };
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
                    <th class="text-left px-5 py-3.5 text-xs font-mono font-medium text-slate-500 uppercase tracking-wider">Paid</th>
                    <th class="text-left px-5 py-3.5 text-xs font-mono font-medium text-slate-500 uppercase tracking-wider">Max Days / Year</th>
                    <th class="text-left px-5 py-3.5 text-xs font-mono font-medium text-slate-500 uppercase tracking-wider">Requires Approval</th>
                    <th class="text-right px-5 py-3.5 text-xs font-mono font-medium text-slate-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @php
                    $canEditLT   = auth()->user()->hasPermission('edit_leave_types');
                    $canDeleteLT = auth()->user()->hasPermission('delete_leave_types');
                @endphp
                @forelse($leaveTypes as $lt)
                    <tr class="hover:bg-stone-50/60 transition-colors">
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-2">
                                @if($lt->color)
                                    <span class="w-3 h-3 rounded-full shrink-0" style="background:{{ $lt->color }}"></span>
                                @endif
                                <span class="font-medium text-slate-800">{{ $lt->name }}</span>
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            @if($lt->is_paid)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-mono font-medium bg-emerald-100 text-emerald-700">Paid</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-mono font-medium bg-slate-100 text-slate-600">Unpaid</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 font-mono text-slate-600 text-xs">
                            {{ $lt->max_days_per_year ? $lt->max_days_per_year . ' days' : '∞ Unlimited' }}
                        </td>
                        <td class="px-5 py-4">
                            @if($lt->requires_approval)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-mono font-medium bg-amber-100 text-amber-700">Yes</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-mono font-medium bg-slate-100 text-slate-600">No</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-right">
                            <div class="inline-flex items-center gap-1.5">
                                @if($canEditLT)
                                <button @click="openEdit({
                                            id:                {{ $lt->id }},
                                            name:              '{{ e($lt->name) }}',
                                            is_paid:           {{ $lt->is_paid ? 'true' : 'false' }},
                                            max_days_per_year: '{{ $lt->max_days_per_year ?? '' }}',
                                            requires_approval: {{ $lt->requires_approval ? 'true' : 'false' }},
                                            color:             '{{ $lt->color ?? '#6366f1' }}'
                                        })"
                                        class="p-1.5 rounded-lg text-slate-400 hover:text-[#E26B3D] hover:bg-[#E26B3D]/10 transition-colors" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </button>
                                @endif
                                @if($canDeleteLT)
                                <button @click="$dispatch('confirm:delete', { action: '{{ route('leave-types.destroy', $lt) }}' })"
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
                            <p class="text-slate-400 font-mono text-sm">No leave types defined yet.</p>
                            @if(auth()->user()->hasPermission('create_leave_types'))
                            <button @click="$dispatch('panel:create')" class="mt-3 text-sm text-[#E26B3D] hover:underline font-mono">Add the first one</button>
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
        @if($leaveTypes->hasPages())
            <div class="px-5 py-3 border-t border-slate-100">{{ $leaveTypes->links() }}</div>
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
            <h2 class="text-base font-semibold text-slate-800" x-text="mode === 'create' ? 'New Leave Type' : 'Edit Leave Type'"></h2>
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
              :action="mode === 'create' ? '{{ route('leave-types.store') }}' : '{{ url('leave-types') }}/' + recordId"
              @submit="submitted = true"
              class="flex-1 overflow-y-auto flex flex-col">
            @csrf
            <input type="hidden" name="_mode" :value="mode">
            <input type="hidden" name="record_id" :value="recordId">

            <div class="px-6 py-6 space-y-6 flex-1">
                <div>
                    <h3 class="text-xs font-mono font-semibold text-slate-400 uppercase tracking-widest mb-4">Leave Type Details</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1.5">Name <span class="text-red-500">*</span></label>
                            <input type="text" name="name" :value="formData.name"
                                   placeholder="e.g. Annual Leave"
                                   class="w-full text-sm border rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#E26B3D]/40 focus:border-[#E26B3D] text-slate-700 placeholder-slate-400 transition-colors"
                                   :class="submitted && !formData.name ? 'border-red-400 ring-1 ring-red-400' : 'border-slate-300'">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1.5">Color</label>
                            <div class="flex items-center gap-3">
                                <input type="color" name="color" x-model="formData.color"
                                       class="w-10 h-10 rounded-lg border border-slate-300 cursor-pointer p-1">
                                <span class="text-sm text-slate-500 font-mono" x-text="formData.color"></span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1.5">Max Days per Year <span class="text-slate-400 font-normal">(leave blank for unlimited)</span></label>
                            <input type="number" name="max_days_per_year" :value="formData.max_days_per_year" min="1"
                                   placeholder="e.g. 21"
                                   class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#E26B3D]/40 focus:border-[#E26B3D] text-slate-700 font-mono transition-colors">
                        </div>
                    </div>
                </div>

                <div class="border-t border-slate-100"></div>

                <div>
                    <h3 class="text-xs font-mono font-semibold text-slate-400 uppercase tracking-widest mb-4">Settings</h3>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between rounded-lg border border-slate-200 px-4 py-3">
                            <div>
                                <p class="text-sm font-medium text-slate-700">Paid Leave</p>
                                <p class="text-xs text-slate-400 mt-0.5">Employee is compensated during this leave</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="is_paid" value="1" class="sr-only peer" x-model="formData.is_paid">
                                <div class="w-10 h-6 bg-slate-200 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#E26B3D]"></div>
                            </label>
                        </div>
                        <div class="flex items-center justify-between rounded-lg border border-slate-200 px-4 py-3">
                            <div>
                                <p class="text-sm font-medium text-slate-700">Requires Approval</p>
                                <p class="text-xs text-slate-400 mt-0.5">Manager must approve all requests of this type</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="requires_approval" value="1" class="sr-only peer" x-model="formData.requires_approval">
                                <div class="w-10 h-6 bg-slate-200 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#E26B3D]"></div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="px-6 py-4 border-t border-slate-100 shrink-0 flex gap-3">
                <button type="submit"
                        class="flex-1 bg-[#E26B3D] hover:bg-[#c8602a] text-white text-sm font-medium py-2.5 rounded-lg transition-colors font-mono"
                        x-text="mode === 'create' ? 'Create Leave Type' : 'Save Changes'"></button>
                <button type="button" @click="close()"
                        class="px-5 py-2.5 text-sm font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors font-mono">Cancel</button>
            </div>
        </form>
    </div>
</div>
@endsection

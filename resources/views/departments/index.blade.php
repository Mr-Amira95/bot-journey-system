@extends('layouts.app')

@section('title', 'Departments')
@section('page-title', 'Departments')

@section('header-actions')
    @if(auth()->user()->hasPermission('create_departments'))
    <button @click="$dispatch('panel:create')"
            class="inline-flex items-center gap-2 rounded-lg bg-[#E26B3D] px-4 py-2 text-sm font-mono font-medium text-white hover:bg-[#c8602a] transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        New Department
    </button>
    @endif
@endsection

@section('content')
<div x-data="{
    open: {{ $errors->any() ? 'true' : 'false' }},
    mode: '{{ old('_method') === 'PUT' ? 'edit' : 'create' }}',
    recordId: {{ old('record_id', 'null') }},
    formData: {
        name: '{{ old('name', '') }}',
        description: '{{ old('description', '') }}'
    },
    openCreate() {
        this.mode = 'create';
        this.recordId = null;
        this.formData = { name: '', description: '' };
        this.open = true;
    },
    openEdit(data) {
        this.mode = 'edit';
        this.recordId = data.id;
        this.formData = data;
        this.open = true;
    },
    close() { this.open = false; }
}" @panel:create.window="openCreate()">

    {{-- Search --}}
    <div class="mb-5">
        <form method="GET" action="{{ route('departments.index') }}" class="flex items-center gap-3">
            <div class="relative flex-1 max-w-sm">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search departments..."
                       class="w-full pl-9 pr-4 py-2 rounded-lg border border-slate-300 bg-white text-sm text-slate-800 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-[#E26B3D] focus:border-[#E26B3D] font-mono">
            </div>
            <button type="submit" class="px-4 py-2 rounded-lg bg-white border border-slate-300 text-sm text-slate-700 hover:bg-stone-50 transition-colors font-mono">
                Search
            </button>
            @if(request('search'))
                <a href="{{ route('departments.index') }}" class="text-sm text-slate-500 hover:text-slate-700 font-mono">Clear</a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-stone-50 border-b border-slate-200">
                <tr>
                    <th class="text-left px-5 py-3.5 text-xs font-mono font-medium text-slate-500 uppercase tracking-wider">Department</th>
                    <th class="text-left px-5 py-3.5 text-xs font-mono font-medium text-slate-500 uppercase tracking-wider">Description</th>
                    <th class="text-center px-5 py-3.5 text-xs font-mono font-medium text-slate-500 uppercase tracking-wider">Employees</th>
                    <th class="text-right px-5 py-3.5 text-xs font-mono font-medium text-slate-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @php
                    $canEditDepts   = auth()->user()->hasPermission('edit_departments');
                    $canDeleteDepts = auth()->user()->hasPermission('delete_departments');
                @endphp
                @forelse($departments as $department)
                <tr class="hover:bg-stone-50/60 transition-colors">
                    <td class="px-5 py-4 font-medium text-slate-800">{{ $department->name }}</td>
                    <td class="px-5 py-4 text-slate-500 max-w-xs truncate font-mono text-xs">{{ $department->description ?: '—' }}</td>
                    <td class="px-5 py-4 text-center">
                        <span class="inline-flex items-center justify-center px-2.5 py-0.5 rounded-full text-xs font-mono font-medium bg-[#E26B3D]/10 text-[#E26B3D]">
                            {{ $department->employees_count }}
                        </span>
                    </td>
                    <td class="px-5 py-4 text-right">
                        <div class="inline-flex items-center gap-2">
                            @if($canEditDepts)
                            <button @click="openEdit({
                                        id: {{ $department->id }},
                                        name: '{{ e($department->name) }}',
                                        description: '{{ e($department->description ?? '') }}'
                                    })"
                                    class="p-1.5 rounded-lg text-slate-400 hover:text-[#E26B3D] hover:bg-[#E26B3D]/10 transition-colors" title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </button>
                            @endif

                            @if($canDeleteDepts)
                            <button @click="$dispatch('confirm:delete', { action: '{{ route('departments.destroy', $department) }}' })"
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
                    <td colspan="4" class="px-5 py-12 text-center text-slate-400">
                        <svg class="w-10 h-10 mx-auto mb-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"/>
                        </svg>
                        <p class="font-medium">No departments found</p>
                        <p class="text-sm mt-1 font-mono">Create your first department to get started.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>

    @if($departments->hasPages())
        <div class="mt-5">{{ $departments->links() }}</div>
    @endif

    {{-- Backdrop --}}
    <div x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="close()"
         class="fixed inset-0 bg-[#0f1b3d]/50 backdrop-blur-sm z-40"
         style="display:none;"></div>

    {{-- Slide-over panel --}}
    <div x-show="open"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="translate-x-full"
         x-transition:enter-end="translate-x-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="translate-x-0"
         x-transition:leave-end="translate-x-full"
         class="fixed right-0 top-0 h-full w-full max-w-md bg-white shadow-2xl z-50 flex flex-col"
         style="display:none;">

        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 shrink-0">
            <h2 class="text-base font-semibold text-slate-800" x-text="mode === 'create' ? 'New Department' : 'Edit Department'"></h2>
            <button @click="close()" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-stone-100 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto px-6 py-5">

            @if($errors->any())
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-3">
                @foreach($errors->all() as $error)
                    <p class="text-sm text-red-600 font-mono">{{ $error }}</p>
                @endforeach
            </div>
            @endif

            <form :action="mode === 'create'
                        ? '{{ route('departments.store') }}'
                        : '{{ url('departments') }}/' + recordId"
                  method="POST" class="space-y-5">
                @csrf
                <input type="hidden" name="_method" :value="mode === 'create' ? 'POST' : 'PUT'">
                <input type="hidden" name="record_id" :value="recordId">

                <div>
                    <label class="block text-xs font-mono font-medium text-slate-600 mb-1.5 uppercase tracking-wider">Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" :value="formData.name"
                           class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#E26B3D] focus:border-[#E26B3D]"
                           placeholder="e.g. Engineering">
                </div>

                <div>
                    <label class="block text-xs font-mono font-medium text-slate-600 mb-1.5 uppercase tracking-wider">Description</label>
                    <textarea name="description" rows="4"
                              x-effect="$el.value = formData.description"
                              class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#E26B3D] focus:border-[#E26B3D] resize-none font-mono"
                              placeholder="Brief description of this department..."></textarea>
                </div>

                <div class="pt-2 flex gap-3">
                    <button type="button" @click="close()"
                            class="flex-1 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-mono font-medium text-slate-700 hover:bg-stone-50 transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                            class="flex-1 rounded-lg bg-[#E26B3D] px-4 py-2.5 text-sm font-mono font-medium text-white hover:bg-[#c8602a] transition-colors">
                        <span x-text="mode === 'create' ? 'Create Department' : 'Save Changes'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

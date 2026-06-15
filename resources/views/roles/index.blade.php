@extends('layouts.app')

@section('title', 'Roles & Permissions')
@section('page-title', 'Roles & Permissions')

@section('header-actions')
    @if(auth()->user()->hasPermission('create_roles'))
    <button @click="$dispatch('panel:create')"
            class="inline-flex items-center gap-2 rounded-lg bg-[#E26B3D] px-4 py-2 text-sm font-mono font-medium text-white hover:bg-[#c8602a] transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        New Role
    </button>
    @endif
@endsection

@section('content')
<div x-data="{
    open: {{ $errors->any() && !session('_permissions_open') ? 'true' : 'false' }},
    mode: '{{ old('_method') === 'PUT' ? 'edit' : 'create' }}',
    recordId: {{ old('record_id', 'null') }},
    formData: {
        name: '{{ old('name', '') }}',
        slug: '{{ old('slug', '') }}'
    },
    permissionsOpen: {{ session('_permissions_open') ? 'true' : 'false' }},
    permissionsRoleId: {{ session('_permissions_role_id', 'null') }},
    permissionsRoleName: '{{ session('_permissions_role_name', '') }}',
    currentPermissions: {{ session('_current_permissions', '[]') }},
    openCreate() {
        this.mode = 'create';
        this.recordId = null;
        this.formData = { name: '', slug: '' };
        this.open = true;
    },
    openEdit(data) {
        this.mode = 'edit';
        this.recordId = data.id;
        this.formData = data;
        this.open = true;
    },
    openPermissions(roleId, roleName, permissions) {
        this.permissionsRoleId = roleId;
        this.permissionsRoleName = roleName;
        this.currentPermissions = permissions;
        this.permissionsOpen = true;
    },
    close() { this.open = false; },
    closePermissions() { this.permissionsOpen = false; },
    generateSlug() {
        this.formData.slug = this.formData.name
            .toLowerCase()
            .trim()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/\s+/g, '-');
    }
}" @panel:create.window="openCreate()">

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-stone-50 border-b border-slate-200">
                <tr>
                    <th class="text-left px-5 py-3.5 text-xs font-mono font-medium text-slate-500 uppercase tracking-wider">Role</th>
                    <th class="text-left px-5 py-3.5 text-xs font-mono font-medium text-slate-500 uppercase tracking-wider">Slug</th>
                    <th class="text-center px-5 py-3.5 text-xs font-mono font-medium text-slate-500 uppercase tracking-wider">Permissions</th>
                    <th class="text-center px-5 py-3.5 text-xs font-mono font-medium text-slate-500 uppercase tracking-wider">Users</th>
                    <th class="text-right px-5 py-3.5 text-xs font-mono font-medium text-slate-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @php
                    $canEditRoles   = auth()->user()->hasPermission('edit_roles');
                    $canDeleteRoles = auth()->user()->hasPermission('delete_roles');
                @endphp
                @forelse($roles as $role)
                <tr class="hover:bg-stone-50/60 transition-colors">
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-[#E26B3D]/10 flex items-center justify-center">
                                <svg class="w-4 h-4 text-[#E26B3D]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                            </div>
                            <span class="font-medium text-slate-800">{{ $role->name }}</span>
                        </div>
                    </td>
                    <td class="px-5 py-4">
                        <code class="text-xs bg-stone-100 text-slate-600 px-2 py-0.5 rounded font-mono">{{ $role->slug }}</code>
                    </td>
                    <td class="px-5 py-4 text-center">
                        <span class="inline-flex items-center justify-center px-2.5 py-0.5 rounded-full text-xs font-mono font-medium bg-violet-50 text-violet-700">
                            {{ $role->permissions_count }}
                        </span>
                    </td>
                    <td class="px-5 py-4 text-center">
                        <span class="inline-flex items-center justify-center px-2.5 py-0.5 rounded-full text-xs font-mono font-medium bg-stone-100 text-slate-600">
                            {{ $role->users_count }}
                        </span>
                    </td>
                    <td class="px-5 py-4 text-right">
                        <div class="inline-flex items-center gap-1.5">
                            @if($canEditRoles)
                            <button @click="openEdit({
                                        id:   {{ $role->id }},
                                        name: '{{ e($role->name) }}',
                                        slug: '{{ e($role->slug) }}'
                                    })"
                                    class="p-1.5 rounded-lg text-slate-400 hover:text-[#E26B3D] hover:bg-[#E26B3D]/10 transition-colors" title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </button>
                            @endif
                            @if($canEditRoles)
                            <button @click="openPermissions(
                                        {{ $role->id }},
                                        '{{ e($role->name) }}',
                                        {{ json_encode($role->permissions->pluck('id')) }}
                                    )"
                                    class="p-1.5 rounded-lg text-slate-400 hover:text-violet-600 hover:bg-violet-50 transition-colors" title="Manage Permissions">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                                </svg>
                            </button>
                            @endif
                            @if($canDeleteRoles)
                            <button @click="$dispatch('confirm:delete', { action: '{{ route('roles.destroy', $role) }}' })"
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
                    <td colspan="5" class="px-5 py-12 text-center text-slate-400">
                        <svg class="w-10 h-10 mx-auto mb-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        <p class="font-medium">No roles found</p>
                        <p class="text-sm mt-1 font-mono">Create your first role to assign permissions.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>

    @if($roles->hasPages())
        <div class="mt-5">{{ $roles->links() }}</div>
    @endif

    {{-- Backdrop (shared) --}}
    <div x-show="open || permissionsOpen"
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         @click="open ? close() : closePermissions()"
         class="fixed inset-0 bg-[#0f1b3d]/50 backdrop-blur-sm z-40" style="display:none;"></div>

    {{-- Role Slide-over --}}
    <div x-show="open"
         x-transition:enter="transition ease-out duration-300" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
         x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
         class="fixed right-0 top-0 h-full w-full max-w-md bg-white shadow-2xl z-50 flex flex-col" style="display:none;">

        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 shrink-0">
            <h2 class="text-base font-semibold text-slate-800" x-text="mode === 'create' ? 'New Role' : 'Edit Role'"></h2>
            <button @click="close()" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-stone-100 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
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

            <form :action="mode === 'create' ? '{{ route('roles.store') }}' : '{{ url('roles') }}/' + recordId"
                  method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="_method" :value="mode === 'create' ? 'POST' : 'PUT'">
                <input type="hidden" name="record_id" :value="recordId">

                <div>
                    <label class="block text-xs font-mono font-medium text-slate-600 mb-1.5 uppercase tracking-wider">Role Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name"
                           x-model="formData.name"
                           @input="if (mode === 'create') generateSlug()"
                           class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#E26B3D]"
                           placeholder="e.g. Manager">
                </div>

                <div>
                    <label class="block text-xs font-mono font-medium text-slate-600 mb-1.5 uppercase tracking-wider">Slug <span class="text-red-500">*</span></label>
                    <input type="text" name="slug"
                           x-model="formData.slug"
                           class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 font-mono focus:outline-none focus:ring-2 focus:ring-[#E26B3D]"
                           placeholder="e.g. manager">
                    <p class="mt-1 text-xs text-slate-400 font-mono">Auto-generated from name. Only letters, numbers, and hyphens.</p>
                </div>

                <div class="pt-2 flex gap-3">
                    <button type="button" @click="close()"
                            class="flex-1 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-mono font-medium text-slate-700 hover:bg-stone-50 transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                            class="flex-1 rounded-lg bg-[#E26B3D] px-4 py-2.5 text-sm font-mono font-medium text-white hover:bg-[#c8602a] transition-colors">
                        <span x-text="mode === 'create' ? 'Create Role' : 'Save Changes'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Permissions Slide-over --}}
    <div x-show="permissionsOpen"
         x-transition:enter="transition ease-out duration-300" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
         x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
         class="fixed right-0 top-0 h-full w-full max-w-lg bg-white shadow-2xl z-50 flex flex-col" style="display:none;">

        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 shrink-0">
            <div>
                <h2 class="text-base font-semibold text-slate-800">Manage Permissions</h2>
                <p class="text-xs text-slate-500 mt-0.5 font-mono" x-text="permissionsRoleName"></p>
            </div>
            <button @click="closePermissions()" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-stone-100 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto px-6 py-5">
            <form :action="'{{ url('roles') }}/' + permissionsRoleId + '/permissions'" method="POST">
                @csrf

                <div class="space-y-6">
                    @foreach($permissions as $module => $modulePermissions)
                    <div>
                        <div class="flex items-center gap-2 mb-3">
                            <span class="text-xs font-mono font-medium text-slate-400 uppercase tracking-widest">{{ $module }}</span>
                            <div class="flex-1 h-px bg-slate-200"></div>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach($modulePermissions as $permission)
                            <label class="flex items-center gap-3 px-3 py-2.5 rounded-lg border border-slate-200 hover:border-[#E26B3D]/40 hover:bg-[#E26B3D]/5 cursor-pointer transition-colors group">
                                <input type="checkbox"
                                       name="permissions[]"
                                       value="{{ $permission->id }}"
                                       :checked="currentPermissions.includes({{ $permission->id }})"
                                       class="w-4 h-4 rounded border-slate-300 text-[#E26B3D] focus:ring-[#E26B3D]">
                                <span class="text-sm text-slate-700 group-hover:text-slate-900 font-mono">{{ $permission->name }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>

                <div class="pt-6 flex gap-3">
                    <button type="button" @click="closePermissions()"
                            class="flex-1 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-mono font-medium text-slate-700 hover:bg-stone-50 transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                            class="flex-1 rounded-lg bg-[#E26B3D] px-4 py-2.5 text-sm font-mono font-medium text-white hover:bg-[#c8602a] transition-colors">
                        Save Permissions
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

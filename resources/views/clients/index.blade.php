@extends('layouts.app')

@section('title', 'Clients')
@section('page-title', 'Clients')

@section('header-actions')
    @if(auth()->user()->hasPermission('create_clients'))
    <button @click="$dispatch('panel:create')"
            class="inline-flex items-center gap-2 rounded-lg bg-[#E26B3D] px-4 py-2 text-sm font-mono font-medium text-white hover:bg-[#c8602a] transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        New Client
    </button>
    @endif
@endsection

@section('content')
<div x-data="{
    open: {{ $errors->any() ? 'true' : 'false' }},
    mode: '{{ old('_mode', 'create') }}',
    recordId: {{ old('record_id', 'null') }},
    attachments: [],
    submitted: false,
    formData: {
        name:            '{{ old('name', '') }}',
        email:           '{{ old('email', '') }}',
        status:          '{{ old('status', 'active') }}',
        company_name:    '{{ old('company_name', '') }}',
        company_website: '{{ old('company_website', '') }}',
        industry:        '{{ old('industry', '') }}',
        notes:           '{{ old('notes', '') }}'
    },
    openCreate() {
        this.mode = 'create';
        this.recordId = null;
        this.attachments = [];
        this.submitted = false;
        this.formData = { name:'', email:'', status:'active', company_name:'', company_website:'', industry:'', notes:'' };
        this.open = true;
    },
    openEdit(data) {
        this.mode = 'edit';
        this.recordId = data.id;
        this.attachments = [];
        this.submitted = false;
        this.formData = data;
        this.open = true;
    },
    addAttachment() { this.attachments.push({ key: '' }); },
    removeAttachment(i) { this.attachments.splice(i, 1); },
    close() { this.open = false; this.submitted = false; }
}" @panel:create.window="openCreate()">

    {{-- Search --}}
    <div class="mb-5">
        <form method="GET" action="{{ route('clients.index') }}" class="flex items-center gap-3">
            <div class="relative flex-1 max-w-sm">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search by company or industry..."
                       class="w-full pl-9 pr-4 py-2 rounded-lg border border-slate-300 bg-white text-sm text-slate-800 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-[#E26B3D] font-mono">
            </div>
            <button type="submit" class="px-4 py-2 rounded-lg bg-white border border-slate-300 text-sm text-slate-700 hover:bg-stone-50 transition-colors font-mono">Search</button>
            @if(request('search'))
                <a href="{{ route('clients.index') }}" class="text-sm text-slate-500 hover:text-slate-700 font-mono">Clear</a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-stone-50 border-b border-slate-200">
                <tr>
                    <th class="text-left px-5 py-3.5 text-xs font-mono font-medium text-slate-500 uppercase tracking-wider">Company</th>
                    <th class="text-left px-5 py-3.5 text-xs font-mono font-medium text-slate-500 uppercase tracking-wider">Industry</th>
                    <th class="text-left px-5 py-3.5 text-xs font-mono font-medium text-slate-500 uppercase tracking-wider">Website</th>
                    <th class="text-left px-5 py-3.5 text-xs font-mono font-medium text-slate-500 uppercase tracking-wider">Contact</th>
                    <th class="text-left px-5 py-3.5 text-xs font-mono font-medium text-slate-500 uppercase tracking-wider">Status</th>
                    <th class="text-right px-5 py-3.5 text-xs font-mono font-medium text-slate-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @php
                    $canEditClients   = auth()->user()->hasPermission('edit_clients');
                    $canDeleteClients = auth()->user()->hasPermission('delete_clients');
                @endphp
                @forelse($clients as $client)
                @php
                    $statusColor = match($client->user?->status?->value) {
                        'active'    => 'bg-green-100 text-green-700',
                        'suspended' => 'bg-red-100 text-red-700',
                        default     => 'bg-slate-100 text-slate-600',
                    };
                @endphp
                <tr class="hover:bg-stone-50/60 transition-colors">
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-3">
                            @if($client->user?->profile_image)
                                <img src="{{ Storage::disk('public')->url($client->user->profile_image) }}"
                                     class="w-8 h-8 rounded-lg object-cover shrink-0" alt="">
                            @else
                                <div class="w-8 h-8 rounded-lg bg-[#E26B3D] flex items-center justify-center text-[#F2EEE5] font-bold text-sm shrink-0">
                                    {{ strtoupper(substr($client->company_name, 0, 1)) }}
                                </div>
                            @endif
                            <span class="font-medium text-slate-800">{{ $client->company_name }}</span>
                        </div>
                    </td>
                    <td class="px-5 py-4 text-slate-600 font-mono text-xs">{{ $client->industry ?: '—' }}</td>
                    <td class="px-5 py-4">
                        @if($client->company_website)
                            <a href="{{ $client->company_website }}" target="_blank" rel="noopener"
                               class="text-[#E26B3D] hover:text-[#c8602a] hover:underline truncate max-w-[160px] inline-block font-mono text-xs">
                                {{ parse_url($client->company_website, PHP_URL_HOST) ?? $client->company_website }}
                            </a>
                        @else
                            <span class="text-slate-400 font-mono text-xs">—</span>
                        @endif
                    </td>
                    <td class="px-5 py-4">
                        @if($client->user)
                            <div>
                                <p class="text-slate-700 text-xs font-medium">{{ $client->user->name }}</p>
                                <p class="text-slate-400 font-mono text-xs">{{ $client->user->email }}</p>
                            </div>
                        @else
                            <span class="text-slate-400 font-mono text-xs">—</span>
                        @endif
                    </td>
                    <td class="px-5 py-4">
                        @if($client->user)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-mono font-medium {{ $statusColor }}">
                                {{ ucfirst($client->user->status->value) }}
                            </span>
                        @else
                            <span class="text-slate-400 font-mono text-xs">—</span>
                        @endif
                    </td>
                    <td class="px-5 py-4 text-right">
                        <div class="inline-flex items-center gap-1.5">
                            <a href="{{ route('clients.show', $client) }}"
                               class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors" title="View">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                            @if($canEditClients)
                            <button @click="openEdit({
                                        id:              {{ $client->id }},
                                        name:            '{{ e($client->user?->name ?? '') }}',
                                        email:           '{{ e($client->user?->email ?? '') }}',
                                        status:          '{{ $client->user?->status?->value ?? 'active' }}',
                                        company_name:    '{{ e($client->company_name) }}',
                                        company_website: '{{ e($client->company_website ?? '') }}',
                                        industry:        '{{ e($client->industry ?? '') }}',
                                        notes:           `{{ e($client->notes ?? '') }}`
                                    })"
                                    class="p-1.5 rounded-lg text-slate-400 hover:text-[#E26B3D] hover:bg-[#E26B3D]/10 transition-colors" title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </button>
                            @endif
                            @if($canDeleteClients)
                            <button @click="$dispatch('confirm:delete', { action: '{{ route('clients.destroy', $client) }}' })"
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
                    <td colspan="6" class="px-5 py-12 text-center text-slate-400">
                        <svg class="w-10 h-10 mx-auto mb-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        <p class="font-medium">No clients found</p>
                        <p class="text-sm mt-1 font-mono">Add your first client to get started.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>

    @if($clients->hasPages())
        <div class="mt-5">{{ $clients->links() }}</div>
    @endif

    {{-- Backdrop --}}
    <div x-show="open"
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         @click="close()"
         class="fixed inset-0 bg-[#0f1b3d]/50 backdrop-blur-sm z-40" style="display:none;"></div>

    {{-- Slide-over --}}
    <div x-show="open"
         x-transition:enter="transition ease-out duration-300" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
         x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
         class="fixed right-0 top-0 h-full w-full max-w-lg bg-white shadow-2xl z-50 flex flex-col" style="display:none;">

        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 shrink-0">
            <h2 class="text-base font-semibold text-slate-800" x-text="mode === 'create' ? 'New Client' : 'Edit Client'"></h2>
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

            <form :action="mode === 'create' ? '{{ route('clients.store') }}' : '{{ url('clients') }}/' + recordId"
                  method="POST" enctype="multipart/form-data" class="space-y-5"
                  @submit="submitted = true">
                @csrf
                <input type="hidden" name="_mode" :value="mode">
                <input type="hidden" name="record_id" :value="recordId">

                {{-- Contact Person --}}
                <div>
                    <p class="text-xs font-mono font-semibold text-[#E26B3D] uppercase tracking-widest mb-3">Contact Person</p>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-mono font-medium text-slate-600 mb-1.5 uppercase tracking-wider">Full Name <span class="text-red-500">*</span></label>
                            <input type="text" name="name" x-model="formData.name"
                                   class="w-full rounded-lg border px-3.5 py-2.5 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#E26B3D]"
                                   :class="submitted && !formData.name.trim() ? 'border-red-400' : 'border-slate-300'"
                                   placeholder="John Doe">
                        </div>
                        <div>
                            <label class="block text-xs font-mono font-medium text-slate-600 mb-1.5 uppercase tracking-wider">Email Address <span class="text-red-500">*</span></label>
                            <input type="email" name="email" x-model="formData.email"
                                   class="w-full rounded-lg border px-3.5 py-2.5 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#E26B3D] font-mono"
                                   :class="submitted && !formData.email.trim() ? 'border-red-400' : 'border-slate-300'"
                                   placeholder="john@client.com">
                        </div>
                        <div x-show="mode === 'create'">
                            <label class="block text-xs font-mono font-medium text-slate-600 mb-1.5 uppercase tracking-wider">Password <span class="text-red-500">*</span></label>
                            <input type="password" name="password" x-ref="password"
                                   class="w-full rounded-lg border px-3.5 py-2.5 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#E26B3D] font-mono"
                                   :class="submitted && mode === 'create' && !$refs.password?.value ? 'border-red-400' : 'border-slate-300'"
                                   placeholder="Min. 8 characters">
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-mono font-medium text-slate-600 mb-1.5 uppercase tracking-wider">Status</label>
                                <select name="status"
                                        x-effect="$el.value = formData.status"
                                        @change="formData.status = $event.target.value"
                                        class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#E26B3D] font-mono">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="suspended">Suspended</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-mono font-medium text-slate-600 mb-1.5 uppercase tracking-wider">Photo</label>
                                <input type="file" name="profile_image" accept="image/*"
                                       class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs text-slate-700 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-xs file:bg-[#E26B3D]/10 file:text-[#E26B3D] hover:file:bg-[#E26B3D]/20 focus:outline-none focus:ring-2 focus:ring-[#E26B3D] font-mono">
                                <p class="mt-1 text-xs text-slate-400 font-mono">Leave blank to auto-generate.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="border-t border-slate-100"></div>

                {{-- Company --}}
                <div>
                    <p class="text-xs font-mono font-semibold text-[#E26B3D] uppercase tracking-widest mb-3">Company Details</p>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-mono font-medium text-slate-600 mb-1.5 uppercase tracking-wider">Company Name <span class="text-red-500">*</span></label>
                            <input type="text" name="company_name" x-model="formData.company_name"
                                   class="w-full rounded-lg border px-3.5 py-2.5 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#E26B3D]"
                                   :class="submitted && !formData.company_name.trim() ? 'border-red-400' : 'border-slate-300'"
                                   placeholder="Acme Corp">
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-mono font-medium text-slate-600 mb-1.5 uppercase tracking-wider">Website</label>
                                <input type="url" name="company_website" :value="formData.company_website"
                                       class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#E26B3D] font-mono"
                                       placeholder="https://example.com">
                            </div>
                            <div>
                                <label class="block text-xs font-mono font-medium text-slate-600 mb-1.5 uppercase tracking-wider">Industry</label>
                                <input type="text" name="industry" :value="formData.industry"
                                       class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#E26B3D]"
                                       placeholder="e.g. Technology">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-mono font-medium text-slate-600 mb-1.5 uppercase tracking-wider">Notes</label>
                            <textarea name="notes" rows="3"
                                      x-effect="$el.value = formData.notes"
                                      class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#E26B3D] resize-none font-mono"
                                      placeholder="Additional notes..."></textarea>
                        </div>
                    </div>
                </div>

                {{-- Attachments (create mode only) --}}
                <div x-show="mode === 'create'" class="border-t border-slate-100"></div>
                <div x-show="mode === 'create'">
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-xs font-mono font-semibold text-[#E26B3D] uppercase tracking-widest">Attachments</p>
                        <button type="button" @click="addAttachment()"
                                class="inline-flex items-center gap-1 text-xs font-mono font-medium text-[#E26B3D] hover:text-[#c8602a] transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Add File
                        </button>
                    </div>
                    <div class="space-y-2.5">
                        <template x-for="(att, i) in attachments" :key="i">
                            <div class="grid grid-cols-5 gap-2 items-start p-3 rounded-lg bg-stone-50 border border-slate-200">
                                <div class="col-span-2">
                                    <label class="block text-xs font-mono text-slate-500 mb-1">Label</label>
                                    <input type="text" :name="`attachments[${i}][key]`" x-model="att.key"
                                           placeholder="e.g. Contract"
                                           class="w-full rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#E26B3D] font-mono">
                                </div>
                                <div class="col-span-2">
                                    <label class="block text-xs font-mono text-slate-500 mb-1">File</label>
                                    <input type="file" :name="`attachments[${i}][file]`"
                                           class="w-full rounded-lg border border-slate-300 px-2 py-1 text-xs text-slate-700 file:mr-1.5 file:py-0.5 file:px-2 file:rounded file:border-0 file:text-xs file:bg-[#E26B3D]/10 file:text-[#E26B3D] hover:file:bg-[#E26B3D]/20 focus:outline-none font-mono">
                                </div>
                                <div class="pt-5">
                                    <button type="button" @click="removeAttachment(i)"
                                            class="w-full flex items-center justify-center p-1.5 rounded-lg text-slate-400 hover:text-red-500 hover:bg-red-50 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </template>
                        <p x-show="attachments.length === 0" class="text-xs font-mono text-slate-400 text-center py-2">No attachments added yet.</p>
                    </div>
                </div>

                <div class="pt-2 flex gap-3">
                    <button type="button" @click="close()"
                            class="flex-1 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-mono font-medium text-slate-700 hover:bg-stone-50 transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                            class="flex-1 rounded-lg bg-[#E26B3D] px-4 py-2.5 text-sm font-mono font-medium text-white hover:bg-[#c8602a] transition-colors">
                        <span x-text="mode === 'create' ? 'Create Client' : 'Save Changes'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

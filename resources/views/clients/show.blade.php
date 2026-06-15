@extends('layouts.app')

@section('title', $client->company_name)
@section('page-title', 'Client Profile')

@section('header-actions')
    <div class="flex items-center gap-3">
        <a href="{{ route('clients.index') }}"
           class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-mono font-medium text-slate-700 hover:bg-stone-50 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back
        </a>
        @if(auth()->user()->hasPermission('edit_clients'))
        <button @click="$dispatch('panel:edit')"
                class="inline-flex items-center gap-2 rounded-lg bg-[#E26B3D] px-4 py-2 text-sm font-mono font-medium text-white hover:bg-[#c8602a] transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            Edit Client
        </button>
        @endif
    </div>
@endsection

@section('content')
@php
    $statusColor = match($client->user?->status?->value) {
        'active'    => 'bg-green-100 text-green-700',
        'suspended' => 'bg-red-100 text-red-700',
        default     => 'bg-slate-100 text-slate-600',
    };
@endphp

<div x-data="{
    open: {{ $errors->any() ? 'true' : 'false' }},
    openEdit() { this.open = true; },
    close() { this.open = false; }
}" @panel:edit.window="openEdit()">

    @include('components.flash-messages')

    <div class="space-y-6">

        {{-- Profile Header --}}
        <div class="bg-white rounded-xl border border-slate-200 p-6">
            <div class="flex items-start gap-5">
                @if($client->user?->profile_image)
                    <img src="{{ Storage::disk('public')->url($client->user->profile_image) }}"
                         class="w-20 h-20 rounded-xl object-cover shrink-0 ring-2 ring-slate-100" alt="">
                @else
                    <div class="w-20 h-20 rounded-xl bg-[#E26B3D] flex items-center justify-center text-[#F2EEE5] font-bold text-3xl shrink-0">
                        {{ strtoupper(substr($client->company_name, 0, 1)) }}
                    </div>
                @endif

                <div class="flex-1 min-w-0">
                    <div class="flex items-start justify-between gap-3 flex-wrap">
                        <div>
                            <h1 class="text-xl font-semibold text-slate-800">{{ $client->company_name }}</h1>
                            @if($client->industry)
                                <p class="text-sm text-slate-500 mt-0.5 font-mono">{{ $client->industry }}</p>
                            @endif
                            @if($client->user)
                                <p class="text-sm text-slate-600 mt-1">
                                    Contact: <span class="font-medium">{{ $client->user->name }}</span>
                                    <span class="text-slate-400 font-mono ml-1">{{ $client->user->email }}</span>
                                </p>
                            @endif
                        </div>
                        <div class="flex items-center gap-2 flex-wrap">
                            @if($client->user)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-mono font-medium {{ $statusColor }}">
                                    {{ ucfirst($client->user->status->value) }}
                                </span>
                            @endif
                            @if($client->company_website)
                                <a href="{{ $client->company_website }}" target="_blank" rel="noopener"
                                   class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-mono font-medium bg-slate-100 text-slate-600 hover:bg-slate-200 transition-colors">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                    </svg>
                                    {{ parse_url($client->company_website, PHP_URL_HOST) }}
                                </a>
                            @endif
                        </div>
                    </div>

                    <div class="mt-4 flex flex-wrap gap-5 text-sm text-slate-500 font-mono">
                        <span class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Added {{ $client->created_at->format('M j, Y') }}
                        </span>
                        <span class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                            </svg>
                            {{ $client->user?->attachments?->count() ?? 0 }} attachment(s)
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- Company Details --}}
            <div class="bg-white rounded-xl border border-slate-200 p-6">
                <h2 class="text-sm font-mono font-semibold text-[#E26B3D] uppercase tracking-widest mb-4">Company Details</h2>
                <dl class="space-y-3.5">
                    <div class="flex justify-between items-start gap-4">
                        <dt class="text-xs font-mono font-medium text-slate-500 uppercase tracking-wider shrink-0">Company</dt>
                        <dd class="text-sm text-slate-800 text-right font-medium">{{ $client->company_name }}</dd>
                    </div>
                    <div class="flex justify-between items-start gap-4">
                        <dt class="text-xs font-mono font-medium text-slate-500 uppercase tracking-wider shrink-0">Industry</dt>
                        <dd class="text-sm text-slate-800 text-right">{{ $client->industry ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between items-start gap-4">
                        <dt class="text-xs font-mono font-medium text-slate-500 uppercase tracking-wider shrink-0">Website</dt>
                        <dd class="text-sm text-right">
                            @if($client->company_website)
                                <a href="{{ $client->company_website }}" target="_blank" rel="noopener"
                                   class="text-[#E26B3D] hover:text-[#c8602a] hover:underline font-mono text-xs">
                                    {{ $client->company_website }}
                                </a>
                            @else
                                <span class="text-slate-400">—</span>
                            @endif
                        </dd>
                    </div>
                    @if($client->notes)
                    <div class="pt-1 border-t border-slate-100">
                        <dt class="text-xs font-mono font-medium text-slate-500 uppercase tracking-wider mb-2">Notes</dt>
                        <dd class="text-sm text-slate-700 leading-relaxed">{{ $client->notes }}</dd>
                    </div>
                    @endif
                </dl>
            </div>

            {{-- Account Details --}}
            <div class="bg-white rounded-xl border border-slate-200 p-6">
                <h2 class="text-sm font-mono font-semibold text-[#E26B3D] uppercase tracking-widest mb-4">Contact Account</h2>
                @if($client->user)
                <dl class="space-y-3.5">
                    <div class="flex justify-between items-start gap-4">
                        <dt class="text-xs font-mono font-medium text-slate-500 uppercase tracking-wider shrink-0">Name</dt>
                        <dd class="text-sm text-slate-800 text-right font-medium">{{ $client->user->name }}</dd>
                    </div>
                    <div class="flex justify-between items-start gap-4">
                        <dt class="text-xs font-mono font-medium text-slate-500 uppercase tracking-wider shrink-0">Email</dt>
                        <dd class="text-sm font-mono text-slate-800 text-right break-all">{{ $client->user->email }}</dd>
                    </div>
                    <div class="flex justify-between items-start gap-4">
                        <dt class="text-xs font-mono font-medium text-slate-500 uppercase tracking-wider shrink-0">Status</dt>
                        <dd class="text-right">
                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-mono font-medium {{ $statusColor }}">
                                {{ ucfirst($client->user->status->value) }}
                            </span>
                        </dd>
                    </div>
                    <div class="flex justify-between items-start gap-4">
                        <dt class="text-xs font-mono font-medium text-slate-500 uppercase tracking-wider shrink-0">Last Login</dt>
                        <dd class="text-sm font-mono text-slate-800 text-right">
                            {{ $client->user->last_login_at ? $client->user->last_login_at->format('M j, Y H:i') : 'Never' }}
                        </dd>
                    </div>
                    <div class="flex justify-between items-start gap-4">
                        <dt class="text-xs font-mono font-medium text-slate-500 uppercase tracking-wider shrink-0">Created</dt>
                        <dd class="text-sm font-mono text-slate-800 text-right">{{ $client->user->created_at->format('M j, Y H:i') }}</dd>
                    </div>
                </dl>
                @else
                <p class="text-sm text-slate-400 font-mono">No contact account linked.</p>
                @endif
            </div>
        </div>

        {{-- Projects --}}
        <div class="bg-white rounded-xl border border-slate-200 p-6">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-sm font-mono font-semibold text-[#E26B3D] uppercase tracking-widest">Projects</h2>
                <span class="text-xs font-mono text-slate-400">{{ $client->projects->count() }} project(s)</span>
            </div>
            @if($client->projects->count() > 0)
            <div class="divide-y divide-slate-100">
                @foreach($client->projects as $project)
                @php
                    $pStatusColor = match($project->status->value) {
                        'planning'  => 'bg-blue-100 text-blue-700',
                        'active'    => 'bg-green-100 text-green-700',
                        'on_hold'   => 'bg-amber-100 text-amber-700',
                        'completed' => 'bg-emerald-100 text-emerald-700',
                        default     => 'bg-slate-100 text-slate-600',
                    };
                @endphp
                <div class="flex items-center justify-between py-3 gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-8 h-8 rounded-lg bg-[#E26B3D]/10 flex items-center justify-center text-[#E26B3D] font-semibold text-sm shrink-0">
                            {{ strtoupper(substr($project->name, 0, 1)) }}
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-slate-800 truncate">{{ $project->name }}</p>
                            <p class="text-xs font-mono text-slate-400">
                                {{ $project->due_date ? 'Due ' . $project->due_date->format('M j, Y') : 'No due date' }}
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 shrink-0">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-mono font-medium {{ $pStatusColor }}">
                            {{ ucwords(str_replace('_', ' ', $project->status->value)) }}
                        </span>
                        <a href="{{ route('projects.show', $project) }}"
                           class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-mono font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 hover:text-slate-800 transition-colors">
                            View
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="text-center py-6 text-slate-400">
                <svg class="w-8 h-8 mx-auto mb-2 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
                <p class="text-sm font-mono">No projects yet.</p>
            </div>
            @endif
        </div>

        {{-- Attachments --}}
        <div class="bg-white rounded-xl border border-slate-200 p-6">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-sm font-mono font-semibold text-[#E26B3D] uppercase tracking-widest">Attachments</h2>
                <span class="text-xs font-mono text-slate-400">{{ $client->user?->attachments?->count() ?? 0 }} file(s)</span>
            </div>

            {{-- Upload Form --}}
            @if($client->user)
            @if(auth()->user()->hasPermission('edit_clients'))
            <form action="{{ route('clients.attachments.store', $client) }}" method="POST"
                  enctype="multipart/form-data"
                  class="mb-6 p-4 rounded-lg border border-dashed border-slate-300 bg-stone-50">
                @csrf
                <p class="text-xs font-mono font-semibold text-slate-500 uppercase tracking-wider mb-3">Upload New Attachment</p>
                <div class="flex flex-wrap gap-3 items-end">
                    <div class="flex-1 min-w-36">
                        <label class="block text-xs font-mono text-slate-500 mb-1">Label / Key</label>
                        <input type="text" name="key" placeholder="e.g. Contract, NDA"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#E26B3D] font-mono"
                               required>
                    </div>
                    <div class="flex-1 min-w-48">
                        <label class="block text-xs font-mono text-slate-500 mb-1">File</label>
                        <input type="file" name="file"
                               class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-xs text-slate-700 file:mr-2 file:py-1 file:px-2.5 file:rounded file:border-0 file:text-xs file:bg-[#E26B3D]/10 file:text-[#E26B3D] hover:file:bg-[#E26B3D]/20 focus:outline-none focus:ring-2 focus:ring-[#E26B3D] font-mono"
                               required>
                    </div>
                    <button type="submit"
                            class="shrink-0 rounded-lg bg-[#E26B3D] px-4 py-2 text-sm font-mono font-medium text-white hover:bg-[#c8602a] transition-colors">
                        Upload
                    </button>
                </div>
            </form>
            @endif
            @endif

            {{-- Attachments List --}}
            @php $canEditClients = auth()->user()->hasPermission('edit_clients'); @endphp
            @if($client->user?->attachments?->count() > 0)
            <div class="divide-y divide-slate-100">
                @foreach($client->user->attachments as $attachment)
                <div class="flex items-center justify-between py-3 gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-slate-800 truncate">{{ $attachment->key }}</p>
                            <p class="text-xs font-mono text-slate-400 truncate">{{ basename($attachment->attachment_path) }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <a href="{{ Storage::disk('public')->url($attachment->attachment_path) }}"
                           target="_blank" download
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-mono font-medium text-slate-600 hover:text-slate-800 bg-slate-100 hover:bg-slate-200 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                            Download
                        </a>
                        @if($canEditClients)
                        <button @click="$dispatch('confirm:delete', { action: '{{ route('clients.attachments.destroy', [$client, $attachment]) }}' })"
                                class="p-1.5 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="text-center py-6 text-slate-400">
                <svg class="w-8 h-8 mx-auto mb-2 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                </svg>
                <p class="text-sm font-mono">No attachments yet.</p>
            </div>
            @endif
        </div>
    </div>

    {{-- Edit Slide-Over Backdrop --}}
    <div x-show="open"
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         @click="close()"
         class="fixed inset-0 bg-[#0f1b3d]/50 backdrop-blur-sm z-40" style="display:none;"></div>

    {{-- Edit Slide-Over --}}
    <div x-show="open"
         x-transition:enter="transition ease-out duration-300" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
         x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
         class="fixed right-0 top-0 h-full w-full max-w-lg bg-white shadow-2xl z-50 flex flex-col" style="display:none;">

        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 shrink-0">
            <h2 class="text-base font-semibold text-slate-800">Edit Client</h2>
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

            <form action="{{ route('clients.update', $client) }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                @csrf

                {{-- Contact Person --}}
                <div>
                    <p class="text-xs font-mono font-semibold text-[#E26B3D] uppercase tracking-widest mb-3">Contact Person</p>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-mono font-medium text-slate-600 mb-1.5 uppercase tracking-wider">Full Name <span class="text-red-500">*</span></label>
                            <input type="text" name="name" value="{{ old('name', $client->user?->name) }}"
                                   class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#E26B3D]">
                        </div>
                        <div>
                            <label class="block text-xs font-mono font-medium text-slate-600 mb-1.5 uppercase tracking-wider">Email Address <span class="text-red-500">*</span></label>
                            <input type="email" name="email" value="{{ old('email', $client->user?->email) }}"
                                   class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#E26B3D] font-mono">
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-mono font-medium text-slate-600 mb-1.5 uppercase tracking-wider">Status</label>
                                <select name="status" class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#E26B3D] font-mono">
                                    @foreach(['active', 'inactive', 'suspended'] as $s)
                                        <option value="{{ $s }}" {{ old('status', $client->user?->status?->value) === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-mono font-medium text-slate-600 mb-1.5 uppercase tracking-wider">Photo</label>
                                <input type="file" name="profile_image" accept="image/*"
                                       class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs text-slate-700 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-xs file:bg-[#E26B3D]/10 file:text-[#E26B3D] hover:file:bg-[#E26B3D]/20 focus:outline-none focus:ring-2 focus:ring-[#E26B3D] font-mono">
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
                            <input type="text" name="company_name" value="{{ old('company_name', $client->company_name) }}"
                                   class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#E26B3D]">
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-mono font-medium text-slate-600 mb-1.5 uppercase tracking-wider">Website</label>
                                <input type="url" name="company_website" value="{{ old('company_website', $client->company_website) }}"
                                       class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#E26B3D] font-mono"
                                       placeholder="https://example.com">
                            </div>
                            <div>
                                <label class="block text-xs font-mono font-medium text-slate-600 mb-1.5 uppercase tracking-wider">Industry</label>
                                <input type="text" name="industry" value="{{ old('industry', $client->industry) }}"
                                       class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#E26B3D]">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-mono font-medium text-slate-600 mb-1.5 uppercase tracking-wider">Notes</label>
                            <textarea name="notes" rows="3"
                                      class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#E26B3D] resize-none font-mono"
                                      placeholder="Additional notes...">{{ old('notes', $client->notes) }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="pt-2 flex gap-3">
                    <button type="button" @click="close()"
                            class="flex-1 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-mono font-medium text-slate-700 hover:bg-stone-50 transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                            class="flex-1 rounded-lg bg-[#E26B3D] px-4 py-2.5 text-sm font-mono font-medium text-white hover:bg-[#c8602a] transition-colors">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

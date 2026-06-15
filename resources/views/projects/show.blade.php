@extends('layouts.app')

@section('title', $project->name)
@section('page-title', 'Project')

@section('header-actions')
    <div class="flex items-center gap-3">
        <a href="{{ route('projects.index') }}"
           class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-mono font-medium text-slate-700 hover:bg-stone-50 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back
        </a>
        <a href="{{ route('tasks.index') }}?project_id={{ $project->id }}&tab=all"
           class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-mono font-medium text-slate-700 hover:bg-stone-50 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
            </svg>
            View Tasks
        </a>
        <a href="{{ route('reports.project', $project) }}"
           target="_blank"
           class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-mono font-medium text-slate-700 hover:bg-stone-50 transition-colors"
           title="Export PDF">
            <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
            Export PDF
        </a>
        @if(auth()->user()->hasPermission('edit_projects'))
        <button @click="$dispatch('panel:edit')"
                class="inline-flex items-center gap-2 rounded-lg bg-[#E26B3D] px-4 py-2 text-sm font-mono font-medium text-white hover:bg-[#c8602a] transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            Edit Project
        </button>
        @endif
    </div>
@endsection

@section('content')
@php
    $statusColor = match($project->status->value) {
        'planning'  => 'bg-blue-100 text-blue-700',
        'active'    => 'bg-green-100 text-green-700',
        'on_hold'   => 'bg-amber-100 text-amber-700',
        'completed' => 'bg-emerald-100 text-emerald-700',
        default     => 'bg-slate-100 text-slate-600',
    };
    $priorityColor = match($project->priority->value) {
        'low'    => 'bg-slate-100 text-slate-600',
        'medium' => 'bg-blue-100 text-blue-700',
        'high'   => 'bg-orange-100 text-orange-700',
        'urgent' => 'bg-red-100 text-red-700',
        default  => 'bg-slate-100 text-slate-600',
    };
    $memberRoleColor = fn($role) => match($role) {
        'owner'   => 'bg-purple-100 text-purple-700',
        'manager' => 'bg-blue-100 text-blue-700',
        'viewer'  => 'bg-slate-100 text-slate-600',
        default   => 'bg-stone-100 text-slate-600',
    };
@endphp

<div x-data="{
    open: {{ $errors->any() ? 'true' : 'false' }},
    openEdit() { this.open = true; },
    close() { this.open = false; }
}" @panel:edit.window="openEdit()">

    @include('components.flash-messages')

    <div class="space-y-6">

        {{-- Header --}}
        <div class="bg-white rounded-xl border border-slate-200 p-6">
            <div class="flex items-start gap-5">
                <div class="w-16 h-16 rounded-xl bg-[#E26B3D] flex items-center justify-center text-[#F2EEE5] font-bold text-2xl shrink-0">
                    {{ strtoupper(substr($project->name, 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-start justify-between gap-3 flex-wrap">
                        <div>
                            <h1 class="text-xl font-semibold text-slate-800">{{ $project->name }}</h1>
                            @if($project->client)
                                <a href="{{ route('clients.show', $project->client) }}"
                                   class="text-sm text-[#E26B3D] hover:text-[#c8602a] hover:underline mt-0.5 inline-block font-mono">
                                    {{ $project->client->company_name }}
                                </a>
                            @endif
                        </div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-mono font-medium {{ $statusColor }}">
                                {{ ucwords(str_replace('_', ' ', $project->status->value)) }}
                            </span>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-mono font-medium {{ $priorityColor }}">
                                {{ ucfirst($project->priority->value) }} Priority
                            </span>
                        </div>
                    </div>
                    <div class="mt-4 flex flex-wrap gap-5 text-sm text-slate-500 font-mono">
                        <span class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            Created by {{ $project->creator?->name ?? '—' }}
                        </span>
                        <span class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            {{ $project->created_at->format('M j, Y') }}
                        </span>
                        <span class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            {{ $project->members->count() }} member(s)
                        </span>
                        <span class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                            </svg>
                            {{ $project->attachments->count() }} attachment(s)
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Details Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- Project Info --}}
            <div class="bg-white rounded-xl border border-slate-200 p-6">
                <h2 class="text-sm font-mono font-semibold text-[#E26B3D] uppercase tracking-widest mb-4">Project Details</h2>
                <dl class="space-y-3.5">
                    <div class="flex justify-between items-start gap-4">
                        <dt class="text-xs font-mono font-medium text-slate-500 uppercase tracking-wider shrink-0">Client</dt>
                        <dd class="text-sm text-right">
                            @if($project->client)
                                <a href="{{ route('clients.show', $project->client) }}"
                                   class="text-[#E26B3D] hover:text-[#c8602a] hover:underline font-medium">
                                    {{ $project->client->company_name }}
                                </a>
                            @else
                                <span class="text-slate-400">—</span>
                            @endif
                        </dd>
                    </div>
                    <div class="flex justify-between items-start gap-4">
                        <dt class="text-xs font-mono font-medium text-slate-500 uppercase tracking-wider shrink-0">Created By</dt>
                        <dd class="text-sm text-slate-800 text-right">{{ $project->creator?->name ?? '—' }}</dd>
                    </div>
                    @if($project->budget)
                    <div class="flex justify-between items-start gap-4">
                        <dt class="text-xs font-mono font-medium text-slate-500 uppercase tracking-wider shrink-0">Budget</dt>
                        <dd class="text-sm text-slate-800 text-right font-mono font-semibold">${{ number_format($project->budget, 2) }}</dd>
                    </div>
                    @endif
                    @if($project->description)
                    <div class="pt-1 border-t border-slate-100">
                        <dt class="text-xs font-mono font-medium text-slate-500 uppercase tracking-wider mb-2">Description</dt>
                        <dd class="text-sm text-slate-700 leading-relaxed">{{ $project->description }}</dd>
                    </div>
                    @endif
                </dl>
            </div>

            {{-- Timeline --}}
            <div class="bg-white rounded-xl border border-slate-200 p-6">
                <h2 class="text-sm font-mono font-semibold text-[#E26B3D] uppercase tracking-widest mb-4">Timeline</h2>
                <dl class="space-y-3.5">
                    <div class="flex justify-between items-start gap-4">
                        <dt class="text-xs font-mono font-medium text-slate-500 uppercase tracking-wider shrink-0">Start Date</dt>
                        <dd class="text-sm font-mono text-slate-800 text-right">{{ $project->start_date?->format('M j, Y') ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between items-start gap-4">
                        <dt class="text-xs font-mono font-medium text-slate-500 uppercase tracking-wider shrink-0">Due Date</dt>
                        <dd class="text-sm font-mono text-right {{ $project->due_date && $project->due_date->isPast() && $project->status->value !== 'completed' ? 'text-red-600 font-semibold' : 'text-slate-800' }}">
                            {{ $project->due_date?->format('M j, Y') ?? '—' }}
                            @if($project->due_date && $project->due_date->isPast() && $project->status->value !== 'completed')
                                <span class="block text-xs font-normal text-red-500">Overdue</span>
                            @endif
                        </dd>
                    </div>
                    @if($project->completed_at)
                    <div class="flex justify-between items-start gap-4">
                        <dt class="text-xs font-mono font-medium text-slate-500 uppercase tracking-wider shrink-0">Completed At</dt>
                        <dd class="text-sm font-mono text-emerald-700 text-right">{{ $project->completed_at->format('M j, Y H:i') }}</dd>
                    </div>
                    @endif
                    <div class="pt-1 border-t border-slate-100">
                        <div class="flex justify-between items-center">
                            <dt class="text-xs font-mono font-medium text-slate-500 uppercase tracking-wider">Current Status</dt>
                            <dd>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-mono font-medium {{ $statusColor }}">
                                    {{ ucwords(str_replace('_', ' ', $project->status->value)) }}
                                </span>
                            </dd>
                        </div>
                    </div>
                </dl>
            </div>
        </div>

        {{-- Team Members --}}
        <div class="bg-white rounded-xl border border-slate-200 p-6">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-sm font-mono font-semibold text-[#E26B3D] uppercase tracking-widest">Team Members</h2>
                <span class="text-xs font-mono text-slate-400">{{ $project->members->count() }} member(s)</span>
            </div>

            {{-- Add Member Form --}}
            <form action="{{ route('projects.members.store', $project) }}" method="POST"
                  class="mb-6 p-4 rounded-lg border border-dashed border-slate-300 bg-stone-50">
                @csrf
                <p class="text-xs font-mono font-semibold text-slate-500 uppercase tracking-wider mb-3">Add Member</p>
                <div class="flex flex-wrap gap-3 items-end">
                    <div class="flex-1 min-w-40">
                        <label class="block text-xs font-mono text-slate-500 mb-1">User</label>
                        <select name="user_id"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#E26B3D] font-mono"
                                required>
                            <option value="">Select user...</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex-1 min-w-36">
                        <label class="block text-xs font-mono text-slate-500 mb-1">Role</label>
                        <select name="role_in_project"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#E26B3D] font-mono"
                                required>
                            @foreach(App\Enums\ProjectMemberRole::cases() as $role)
                                <option value="{{ $role->value }}">{{ ucfirst($role->value) }}</option>
                            @endforeach
                        </select>
                    </div>
                    @if(auth()->user()->hasPermission('edit_projects'))
                    <button type="submit"
                            class="shrink-0 rounded-lg bg-[#E26B3D] px-4 py-2 text-sm font-mono font-medium text-white hover:bg-[#c8602a] transition-colors">
                        Add
                    </button>
                    @endif
                </div>
            </form>

            {{-- Members List --}}
            @php $canEditProjects = auth()->user()->hasPermission('edit_projects'); @endphp
            @if($project->members->count() > 0)
            <div class="divide-y divide-slate-100">
                @foreach($project->members as $member)
                <div class="flex items-center justify-between py-3 gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-8 h-8 rounded-full bg-[#E26B3D] flex items-center justify-center text-white text-xs font-semibold shrink-0">
                            {{ strtoupper(substr($member->user->name, 0, 1)) }}
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-slate-800 truncate">{{ $member->user->name }}</p>
                            <p class="text-xs font-mono text-slate-400 truncate">{{ $member->user->email }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 shrink-0">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-mono font-medium {{ $memberRoleColor($member->role_in_project->value) }}">
                            {{ ucfirst($member->role_in_project->value) }}
                        </span>
                        @if($member->joined_at)
                            <span class="text-xs font-mono text-slate-400 hidden sm:block">{{ $member->joined_at->format('M j, Y') }}</span>
                        @endif
                        @if($canEditProjects)
                        <button @click="$dispatch('confirm:delete', { action: '{{ route('projects.members.destroy', [$project, $member]) }}' })"
                                class="p-1.5 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
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
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <p class="text-sm font-mono">No members yet.</p>
            </div>
            @endif
        </div>

        {{-- Attachments --}}
        <div class="bg-white rounded-xl border border-slate-200 p-6">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-sm font-mono font-semibold text-[#E26B3D] uppercase tracking-widest">Attachments</h2>
                <span class="text-xs font-mono text-slate-400">{{ $project->attachments->count() }} file(s)</span>
            </div>

            {{-- Upload Form --}}
            @if(auth()->user()->hasPermission('edit_projects'))
            <form action="{{ route('projects.attachments.store', $project) }}" method="POST"
                  enctype="multipart/form-data"
                  class="mb-6 p-4 rounded-lg border border-dashed border-slate-300 bg-stone-50">
                @csrf
                <p class="text-xs font-mono font-semibold text-slate-500 uppercase tracking-wider mb-3">Upload New Attachment</p>
                <div class="flex flex-wrap gap-3 items-end">
                    <div class="flex-1 min-w-36">
                        <label class="block text-xs font-mono text-slate-500 mb-1">Label</label>
                        <input type="text" name="file_name" placeholder="e.g. Brief, Contract"
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

            {{-- Attachments List --}}
            @php $canEditProjectsAttach = auth()->user()->hasPermission('edit_projects'); @endphp
            @if($project->attachments->count() > 0)
            <div class="divide-y divide-slate-100">
                @foreach($project->attachments as $attachment)
                <div class="flex items-center justify-between py-3 gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-slate-800 truncate">{{ $attachment->file_name }}</p>
                            <p class="text-xs font-mono text-slate-400 truncate">
                                {{ basename($attachment->file_path) }}
                                @if($attachment->uploader) · by {{ $attachment->uploader->name }}@endif
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <a href="{{ Storage::disk('public')->url($attachment->file_path) }}"
                           target="_blank" download
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-mono font-medium text-slate-600 hover:text-slate-800 bg-slate-100 hover:bg-slate-200 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                            Download
                        </a>
                        @if($canEditProjectsAttach)
                        <button @click="$dispatch('confirm:delete', { action: '{{ route('projects.attachments.destroy', [$project, $attachment]) }}' })"
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

        {{-- Activity Log --}}
        @if($project->statusLogs->count() > 0)
        <div class="bg-white rounded-xl border border-slate-200 p-6">
            <h2 class="text-sm font-mono font-semibold text-[#E26B3D] uppercase tracking-widest mb-4">Activity Log</h2>
            <div class="space-y-3">
                @foreach($project->statusLogs->sortByDesc('log_at') as $log)
                <div class="flex items-start gap-3">
                    <div class="w-1.5 h-1.5 rounded-full bg-[#E26B3D] mt-2 shrink-0"></div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-slate-700">{{ ucfirst(str_replace('_', ' ', $log->log_key)) }}</p>
                        <p class="text-xs font-mono text-slate-400 mt-0.5">
                            {{ $log->changedBy?->name ?? 'Unknown' }} · {{ \Carbon\Carbon::parse($log->log_at)->format('M j, Y H:i') }}
                        </p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

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
            <h2 class="text-base font-semibold text-slate-800">Edit Project</h2>
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

            <form action="{{ route('projects.update', $project) }}" method="POST" class="space-y-5">
                @csrf

                {{-- Project Details --}}
                <div>
                    <p class="text-xs font-mono font-semibold text-[#E26B3D] uppercase tracking-widest mb-3">Project Details</p>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-mono font-medium text-slate-600 mb-1.5 uppercase tracking-wider">Project Name <span class="text-red-500">*</span></label>
                            <input type="text" name="name" value="{{ old('name', $project->name) }}"
                                   class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#E26B3D]"
                                   required>
                        </div>
                        <div>
                            <label class="block text-xs font-mono font-medium text-slate-600 mb-1.5 uppercase tracking-wider">Description</label>
                            <textarea name="description" rows="3"
                                      class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#E26B3D] resize-none font-mono"
                                      placeholder="Brief project overview...">{{ old('description', $project->description) }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="border-t border-slate-100"></div>

                {{-- Client & Settings --}}
                <div>
                    <p class="text-xs font-mono font-semibold text-[#E26B3D] uppercase tracking-widest mb-3">Client & Settings</p>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-mono font-medium text-slate-600 mb-1.5 uppercase tracking-wider">Client <span class="text-red-500">*</span></label>
                            <select name="client_id"
                                    class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#E26B3D] font-mono"
                                    required>
                                <option value="">Select a client...</option>
                                @foreach($clients as $c)
                                    <option value="{{ $c->id }}" {{ old('client_id', $project->client_id) == $c->id ? 'selected' : '' }}>
                                        {{ $c->company_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-mono font-medium text-slate-600 mb-1.5 uppercase tracking-wider">Status</label>
                                <select name="status" class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#E26B3D] font-mono">
                                    @foreach(App\Enums\ProjectStatus::cases() as $s)
                                        <option value="{{ $s->value }}" {{ old('status', $project->status->value) === $s->value ? 'selected' : '' }}>
                                            {{ ucwords(str_replace('_', ' ', $s->value)) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-mono font-medium text-slate-600 mb-1.5 uppercase tracking-wider">Priority</label>
                                <select name="priority" class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#E26B3D] font-mono">
                                    @foreach(App\Enums\ProjectPriority::cases() as $p)
                                        <option value="{{ $p->value }}" {{ old('priority', $project->priority->value) === $p->value ? 'selected' : '' }}>
                                            {{ ucfirst($p->value) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-mono font-medium text-slate-600 mb-1.5 uppercase tracking-wider">Budget</label>
                            <div class="relative">
                                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm font-mono">$</span>
                                <input type="number" name="budget" value="{{ old('budget', $project->budget) }}" min="0" step="0.01"
                                       class="w-full rounded-lg border border-slate-300 pl-7 pr-3.5 py-2.5 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#E26B3D] font-mono"
                                       placeholder="0.00">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="border-t border-slate-100"></div>

                {{-- Timeline --}}
                <div>
                    <p class="text-xs font-mono font-semibold text-[#E26B3D] uppercase tracking-widest mb-3">Timeline</p>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-mono font-medium text-slate-600 mb-1.5 uppercase tracking-wider">Start Date</label>
                            <input type="date" name="start_date" value="{{ old('start_date', $project->start_date?->format('Y-m-d')) }}"
                                   class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#E26B3D] font-mono">
                        </div>
                        <div>
                            <label class="block text-xs font-mono font-medium text-slate-600 mb-1.5 uppercase tracking-wider">Due Date</label>
                            <input type="date" name="due_date" value="{{ old('due_date', $project->due_date?->format('Y-m-d')) }}"
                                   class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#E26B3D] font-mono">
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

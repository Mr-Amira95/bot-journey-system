@extends('layouts.app')

@section('title', $employee->user->name)
@section('page-title', 'Employee Profile')

@section('header-actions')
    <div class="flex items-center gap-3 flex-wrap">
        <a href="{{ route('employees.index') }}"
           class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-mono font-medium text-slate-700 hover:bg-stone-50 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back
        </a>
        <a href="{{ route('tasks.index') }}?tab=all&assigned_to={{ $employee->user_id }}"
           class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-mono font-medium text-slate-700 hover:bg-stone-50 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
            </svg>
            View Tasks
        </a>

        {{-- Send Job Offer --}}
        @if(auth()->user()->hasPermission('send_employee_documents'))
        <form method="POST" action="{{ route('employees.send-job-offer', $employee) }}">
            @csrf
            <button type="submit"
                    class="inline-flex items-center gap-2 rounded-lg border border-blue-300 bg-blue-50 px-4 py-2 text-sm font-mono font-medium text-blue-700 hover:bg-blue-100 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                Send Job Offer
            </button>
        </form>

        {{-- Send Contract --}}
        <form method="POST" action="{{ route('employees.send-contract', $employee) }}">
            @csrf
            <button type="submit"
                    class="inline-flex items-center gap-2 rounded-lg border border-emerald-300 bg-emerald-50 px-4 py-2 text-sm font-mono font-medium text-emerald-700 hover:bg-emerald-100 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Send Contract
            </button>
        </form>
        @endif

        @if(auth()->user()->hasPermission('edit_employees'))
        <form method="POST" action="{{ route('employees.send-reset-password', $employee) }}">
            @csrf
            <button type="submit"
                    class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-mono font-medium text-slate-700 hover:bg-stone-50 transition-colors">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                Reset Password
            </button>
        </form>
        @endif

        @if(auth()->user()->hasPermission('edit_employees'))
        <button @click="$dispatch('panel:edit')"
                class="inline-flex items-center gap-2 rounded-lg bg-[#E26B3D] px-4 py-2 text-sm font-mono font-medium text-white hover:bg-[#c8602a] transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            Edit Employee
        </button>
        @endif
    </div>
@endsection

@section('content')
@php
    $status = $employee->user->status;
    $statusColor = match($status->value) {
        'active'    => 'bg-green-100 text-green-700',
        'suspended' => 'bg-red-100 text-red-700',
        default     => 'bg-slate-100 text-slate-600',
    };
    $typeColor = $employee->type->value === 'hourly_employee'
        ? 'bg-blue-100 text-blue-700'
        : 'bg-purple-100 text-purple-700';
@endphp

<div x-data="{
    open: {{ $errors->any() ? 'true' : 'false' }},
    openEdit() { this.open = true; },
    close() { this.open = false; }
}" @panel:edit.window="openEdit()">

    {{-- Flash messages --}}
    @include('components.flash-messages')

    <div class="space-y-6">

        {{-- Profile Header --}}
        <div class="bg-white rounded-xl border border-slate-200 p-6">
            <div class="flex items-start gap-5">
                {{-- Avatar --}}
                @if($employee->user->profile_image)
                    <img src="{{ Storage::disk('public')->url($employee->user->profile_image) }}"
                         class="w-20 h-20 rounded-xl object-cover shrink-0 ring-2 ring-slate-100" alt="">
                @else
                    <div class="w-20 h-20 rounded-xl bg-[#E26B3D]/15 flex items-center justify-center text-[#E26B3D] font-bold text-3xl shrink-0">
                        {{ strtoupper(substr($employee->user->name, 0, 1)) }}
                    </div>
                @endif

                <div class="flex-1 min-w-0">
                    <div class="flex items-start justify-between gap-3 flex-wrap">
                        <div>
                            <h1 class="text-xl font-semibold text-slate-800">{{ $employee->user->name }}</h1>
                            <p class="text-sm font-mono text-slate-500 mt-0.5">{{ $employee->user->email }}</p>
                            <p class="text-sm text-slate-600 mt-1">{{ $employee->position ?? '—' }}
                                @if($employee->department)
                                    <span class="text-slate-400">·</span> {{ $employee->department->name }}
                                @endif
                            </p>
                        </div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-mono font-medium {{ $statusColor }}">
                                {{ ucfirst($status->value) }}
                            </span>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-mono font-medium {{ $typeColor }}">
                                {{ ucwords(str_replace('_', ' ', $employee->type->value)) }}
                            </span>
                        </div>
                    </div>

                    <div class="mt-4 flex flex-wrap gap-5 text-sm text-slate-500 font-mono">
                        <span class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            Hired {{ $employee->hire_date?->format('M j, Y') ?? '—' }}
                        </span>
                        @if($employee->manager)
                        <span class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            Reports to {{ $employee->manager->user->name }}
                        </span>
                        @endif
                        <span class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Member since {{ $employee->user->created_at->format('M j, Y') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- Employment Details --}}
            <div class="bg-white rounded-xl border border-slate-200 p-6">
                <h2 class="text-sm font-mono font-semibold text-[#E26B3D] uppercase tracking-widest mb-4">Employment Details</h2>
                <dl class="space-y-3.5">
                    <div class="flex justify-between items-start gap-4">
                        <dt class="text-xs font-mono font-medium text-slate-500 uppercase tracking-wider shrink-0">Department</dt>
                        <dd class="text-sm text-slate-800 text-right">{{ $employee->department?->name ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between items-start gap-4">
                        <dt class="text-xs font-mono font-medium text-slate-500 uppercase tracking-wider shrink-0">Position</dt>
                        <dd class="text-sm text-slate-800 text-right">{{ $employee->position ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between items-start gap-4">
                        <dt class="text-xs font-mono font-medium text-slate-500 uppercase tracking-wider shrink-0">Manager</dt>
                        <dd class="text-sm text-slate-800 text-right">{{ $employee->manager?->user->name ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between items-start gap-4">
                        <dt class="text-xs font-mono font-medium text-slate-500 uppercase tracking-wider shrink-0">Hire Date</dt>
                        <dd class="text-sm font-mono text-slate-800 text-right">{{ $employee->hire_date?->format('Y-m-d') ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between items-start gap-4">
                        <dt class="text-xs font-mono font-medium text-slate-500 uppercase tracking-wider shrink-0">Type</dt>
                        <dd class="text-sm text-slate-800 text-right">
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-mono font-medium {{ $typeColor }}">
                                {{ ucwords(str_replace('_', ' ', $employee->type->value)) }}
                            </span>
                        </dd>
                    </div>
                    @if($employee->salary !== null)
                    <div class="flex justify-between items-start gap-4">
                        <dt class="text-xs font-mono font-medium text-slate-500 uppercase tracking-wider shrink-0">Monthly Salary</dt>
                        <dd class="text-sm font-mono text-slate-800 text-right">${{ number_format($employee->salary, 2) }}</dd>
                    </div>
                    @endif
                    @if($employee->hourly_rate !== null)
                    <div class="flex justify-between items-start gap-4">
                        <dt class="text-xs font-mono font-medium text-slate-500 uppercase tracking-wider shrink-0">Hourly Rate</dt>
                        <dd class="text-sm font-mono text-slate-800 text-right">${{ number_format($employee->hourly_rate, 2) }}/hr</dd>
                    </div>
                    @endif
                </dl>
            </div>

            {{-- Account Details --}}
            <div class="bg-white rounded-xl border border-slate-200 p-6">
                <h2 class="text-sm font-mono font-semibold text-[#E26B3D] uppercase tracking-widest mb-4">Account Details</h2>
                <dl class="space-y-3.5">
                    <div class="flex justify-between items-start gap-4">
                        <dt class="text-xs font-mono font-medium text-slate-500 uppercase tracking-wider shrink-0">Email</dt>
                        <dd class="text-sm font-mono text-slate-800 text-right break-all">{{ $employee->user->email }}</dd>
                    </div>
                    <div class="flex justify-between items-start gap-4">
                        <dt class="text-xs font-mono font-medium text-slate-500 uppercase tracking-wider shrink-0">Status</dt>
                        <dd class="text-right">
                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-mono font-medium {{ $statusColor }}">
                                {{ ucfirst($status->value) }}
                            </span>
                        </dd>
                    </div>
                    <div class="flex justify-between items-start gap-4">
                        <dt class="text-xs font-mono font-medium text-slate-500 uppercase tracking-wider shrink-0">Last Login</dt>
                        <dd class="text-sm font-mono text-slate-800 text-right">
                            {{ $employee->user->last_login_at ? $employee->user->last_login_at->format('M j, Y H:i') : 'Never' }}
                        </dd>
                    </div>
                    <div class="flex justify-between items-start gap-4">
                        <dt class="text-xs font-mono font-medium text-slate-500 uppercase tracking-wider shrink-0">Created</dt>
                        <dd class="text-sm font-mono text-slate-800 text-right">{{ $employee->user->created_at->format('M j, Y H:i') }}</dd>
                    </div>
                    <div class="flex justify-between items-start gap-4">
                        <dt class="text-xs font-mono font-medium text-slate-500 uppercase tracking-wider shrink-0">Updated</dt>
                        <dd class="text-sm font-mono text-slate-800 text-right">{{ $employee->user->updated_at->format('M j, Y H:i') }}</dd>
                    </div>
                    @if($employee->subordinates->count() > 0)
                    <div class="flex justify-between items-start gap-4">
                        <dt class="text-xs font-mono font-medium text-slate-500 uppercase tracking-wider shrink-0">Direct Reports</dt>
                        <dd class="text-sm text-slate-800 text-right">
                            <div class="space-y-1">
                                @foreach($employee->subordinates as $sub)
                                    <div>
                                        <a href="{{ route('employees.show', $sub) }}" class="text-[#E26B3D] hover:text-[#c8602a] hover:underline">
                                            {{ $sub->user->name }}
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </dd>
                    </div>
                    @endif
                </dl>
            </div>
        </div>

        {{-- Projects --}}
        <div class="bg-white rounded-xl border border-slate-200 p-6">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-sm font-mono font-semibold text-[#E26B3D] uppercase tracking-widest">Projects</h2>
                <span class="text-xs font-mono text-slate-400">{{ $employeeProjects->count() }} project(s)</span>
            </div>
            @if($employeeProjects->count() > 0)
            <div class="divide-y divide-slate-100">
                @foreach($employeeProjects as $project)
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
                                {{ $project->client?->company_name ?? 'No client' }}
                                @if($project->due_date)· Due {{ $project->due_date->format('M j, Y') }}@endif
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
                <p class="text-sm font-mono">Not a member of any projects.</p>
            </div>
            @endif
        </div>

        {{-- Attachments --}}
        @if(auth()->user()->hasPermission('view_employee_attachments'))
        <div class="bg-white rounded-xl border border-slate-200 p-6">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-sm font-mono font-semibold text-[#E26B3D] uppercase tracking-widest">Attachments</h2>
                <div class="flex items-center gap-3">
                    <span class="text-xs font-mono text-slate-400">{{ $employee->user->attachments->count() }} file(s)</span>
                    @if(auth()->user()->hasPermission('send_employee_documents'))
                    <form method="POST" action="{{ route('employees.regenerate-documents', $employee) }}">
                        @csrf
                        <button type="submit"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-mono font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 hover:text-slate-800 transition-colors"
                                title="Re-generate Job Offer, NDA, and Contract PDFs">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Regenerate Docs
                        </button>
                    </form>
                    @endif
                </div>
            </div>

            {{-- Upload Form --}}
            @if(auth()->user()->hasPermission('manage_employee_attachments'))
            <form action="{{ route('employees.attachments.store', $employee) }}" method="POST"
                  enctype="multipart/form-data"
                  class="mb-6 p-4 rounded-lg border border-dashed border-slate-300 bg-stone-50">
                @csrf
                <p class="text-xs font-mono font-semibold text-slate-500 uppercase tracking-wider mb-3">Upload New Attachment</p>
                <div class="flex flex-wrap gap-3 items-end">
                    <div class="flex-1 min-w-36">
                        <label class="block text-xs font-mono text-slate-500 mb-1">Label / Key</label>
                        <input type="text" name="key" placeholder="e.g. Contract, ID Copy"
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
            @if($employee->user->attachments->count() > 0)
            <div class="divide-y divide-slate-100">
                @foreach($employee->user->attachments as $attachment)
                @php
                    $isGenerated = in_array($attachment->key, ['job_offer', 'nda', 'contract']);
                    $docLabels   = ['job_offer' => 'Job Offer', 'nda' => 'NDA', 'contract' => 'Contract'];
                @endphp
                <div class="flex items-center justify-between py-3 gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-8 h-8 rounded-lg {{ $isGenerated ? 'bg-[#E26B3D]/10' : 'bg-slate-100' }} flex items-center justify-center shrink-0">
                            @if($isGenerated)
                            <svg class="w-4 h-4 text-[#E26B3D]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            @else
                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                            </svg>
                            @endif
                        </div>
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <p class="text-sm font-medium text-slate-800 truncate">{{ $docLabels[$attachment->key] ?? $attachment->key }}</p>
                                @if($isGenerated)
                                <span class="inline-flex px-1.5 py-0.5 rounded text-xs font-mono font-medium bg-[#E26B3D]/10 text-[#E26B3D]">Auto</span>
                                @endif
                            </div>
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
                        @if(auth()->user()->hasPermission('manage_employee_attachments'))
                        <button @click="$dispatch('confirm:delete', { action: '{{ route('employees.attachments.destroy', [$employee, $attachment]) }}' })"
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
            <h2 class="text-base font-semibold text-slate-800">Edit Employee</h2>
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

            <form action="{{ route('employees.update', $employee) }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                @csrf

                {{-- Section: Account --}}
                <div>
                    <p class="text-xs font-mono font-semibold text-[#E26B3D] uppercase tracking-widest mb-3">Account Information</p>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-mono font-medium text-slate-600 mb-1.5 uppercase tracking-wider">Full Name <span class="text-red-500">*</span></label>
                            <input type="text" name="name" value="{{ old('name', $employee->user->name) }}"
                                   class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#E26B3D]">
                        </div>
                        <div>
                            <label class="block text-xs font-mono font-medium text-slate-600 mb-1.5 uppercase tracking-wider">Email Address <span class="text-red-500">*</span></label>
                            <input type="email" name="email" value="{{ old('email', $employee->user->email) }}"
                                   class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#E26B3D] font-mono">
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-mono font-medium text-slate-600 mb-1.5 uppercase tracking-wider">Status</label>
                                <select name="status" class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#E26B3D] font-mono">
                                    @foreach(['active', 'inactive', 'suspended'] as $s)
                                        <option value="{{ $s }}" {{ old('status', $employee->user->status->value) === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-mono font-medium text-slate-600 mb-1.5 uppercase tracking-wider">Profile Photo</label>
                                <input type="file" name="profile_image" accept="image/*"
                                       class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs text-slate-700 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-xs file:bg-[#E26B3D]/10 file:text-[#E26B3D] hover:file:bg-[#E26B3D]/20 focus:outline-none focus:ring-2 focus:ring-[#E26B3D] font-mono">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="border-t border-slate-100"></div>

                {{-- Section: Employment --}}
                <div x-data="{ type: '{{ old('type', $employee->type->value) }}' }">
                    <p class="text-xs font-mono font-semibold text-[#E26B3D] uppercase tracking-widest mb-3">Employment Details</p>
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-mono font-medium text-slate-600 mb-1.5 uppercase tracking-wider">Department <span class="text-red-500">*</span></label>
                                <select name="department_id" class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#E26B3D] font-mono">
                                    <option value="">Select dept.</option>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept->id }}" {{ old('department_id', $employee->department_id) == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-mono font-medium text-slate-600 mb-1.5 uppercase tracking-wider">Manager</label>
                                <select name="manager_id" class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#E26B3D] font-mono">
                                    <option value="">No manager</option>
                                    @foreach($managers as $manager)
                                        <option value="{{ $manager->id }}" {{ old('manager_id', $employee->manager_id) == $manager->id ? 'selected' : '' }}>{{ $manager->user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-mono font-medium text-slate-600 mb-1.5 uppercase tracking-wider">Position / Title <span class="text-red-500">*</span></label>
                            <input type="text" name="position" value="{{ old('position', $employee->position) }}"
                                   class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#E26B3D]">
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-mono font-medium text-slate-600 mb-1.5 uppercase tracking-wider">Type <span class="text-red-500">*</span></label>
                                <select name="type" @change="type = $event.target.value"
                                        class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#E26B3D] font-mono">
                                    @foreach($employeeTypes as $et)
                                        <option value="{{ $et->value }}" {{ old('type', $employee->type->value) === $et->value ? 'selected' : '' }}>
                                            {{ ucwords(str_replace('_', ' ', $et->value)) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-mono font-medium text-slate-600 mb-1.5 uppercase tracking-wider">Hire Date <span class="text-red-500">*</span></label>
                                <input type="date" name="hire_date" value="{{ old('hire_date', $employee->hire_date?->format('Y-m-d')) }}"
                                       class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#E26B3D] font-mono">
                            </div>
                        </div>

                        <div x-show="type === 'contract_employee'">
                            <label class="block text-xs font-mono font-medium text-slate-600 mb-1.5 uppercase tracking-wider">Monthly Salary</label>
                            <div class="relative">
                                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm font-mono">$</span>
                                <input type="number" name="salary" value="{{ old('salary', $employee->salary) }}" min="0" step="0.01"
                                       class="w-full rounded-lg border border-slate-300 pl-7 pr-3.5 py-2.5 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#E26B3D] font-mono">
                            </div>
                        </div>

                        <div x-show="type === 'hourly_employee'">
                            <label class="block text-xs font-mono font-medium text-slate-600 mb-1.5 uppercase tracking-wider">Hourly Rate</label>
                            <div class="relative">
                                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm font-mono">$</span>
                                <input type="number" name="hourly_rate" value="{{ old('hourly_rate', $employee->hourly_rate) }}" min="0" step="0.01"
                                       class="w-full rounded-lg border border-slate-300 pl-7 pr-3.5 py-2.5 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#E26B3D] font-mono">
                            </div>
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

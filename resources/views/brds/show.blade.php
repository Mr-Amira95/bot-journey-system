@extends('layouts.app')

@section('title', $brd->title)
@section('page-title', 'BRD')

@section('header-actions')
    <div class="flex items-center gap-3">
        <a href="{{ route('brds.index') }}"
           class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-stone-50 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back
        </a>
        @if($canExport)
        <a href="{{ route('brds.export', $brd) }}"
           class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-stone-50 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
            Export PDF
        </a>
        @endif
        @if($canEditBrd && $brd->status->value !== 'approved')
        <a href="{{ route('brds.edit', $brd) }}"
           class="inline-flex items-center gap-2 rounded-lg bg-[#E26B3D] px-4 py-2 text-sm font-medium text-white hover:bg-[#c85a2f] transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            Edit BRD
        </a>
        @endif
    </div>
@endsection

@section('content')
@php
    $statusStyles = [
        'pending'  => ['badge' => 'bg-amber-100 text-amber-700',  'bar' => 'bg-amber-400',  'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
        'approved' => ['badge' => 'bg-emerald-100 text-emerald-700', 'bar' => 'bg-emerald-400', 'icon' => 'M5 13l4 4L19 7'],
        'rejected' => ['badge' => 'bg-red-100 text-red-700', 'bar' => 'bg-red-400', 'icon' => 'M6 18L18 6M6 6l12 12'],
    ];
    $priorityColors = [
        'low'    => 'bg-slate-100 text-slate-600',
        'medium' => 'bg-blue-100 text-blue-700',
        'high'   => 'bg-red-100 text-red-700',
    ];
    $statusStyle = $statusStyles[$brd->status->value] ?? $statusStyles['pending'];
    $isOwner = $brd->created_by === auth()->id();
    $canDecide = $brd->status->value === 'pending' && $canApprove && !$isOwner;
    $kpiLines = $brd->kpis ? array_values(array_filter(array_map('trim', explode("\n", $brd->kpis)), fn ($l) => $l !== '')) : [];
@endphp

<div x-data="{
        confirmDelete() { $dispatch('confirm:delete', { action: '{{ route('brds.destroy', $brd) }}' }); },
        showReject: false,
     }"
     class="max-w-6xl">

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
        {{-- Main column --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Header card --}}
            <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                <div class="h-1.5 {{ $statusStyle['bar'] }}"></div>
                <div class="p-6">
                    <div class="flex items-start justify-between gap-4 mb-2">
                        <h1 class="text-xl font-semibold text-slate-800">{{ $brd->title }}</h1>
                        <div class="flex items-center gap-2 shrink-0">
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium {{ $statusStyle['badge'] }}">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="{{ $statusStyle['icon'] }}"/>
                                </svg>
                                {{ ucfirst($brd->status->value) }}
                            </span>
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $priorityColors[$brd->priority->value] ?? 'bg-slate-100 text-slate-600' }}">
                                {{ ucfirst($brd->priority->value) }} priority
                            </span>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-slate-500 mb-5">
                        <span class="inline-flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                            </svg>
                            <a href="{{ route('projects.show', $brd->project) }}" class="hover:text-[#E26B3D] transition-colors">{{ $brd->project?->name }}</a>
                        </span>
                        @if($brd->department)
                        <span class="text-slate-300">·</span>
                        <span class="inline-flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16M4 21h16M9 7h1m4 0h1M9 11h1m4 0h1M9 15h1m4 0h1"/>
                            </svg>
                            {{ $brd->department }}
                        </span>
                        @endif
                        @if($brd->direct_manager)
                        <span class="text-slate-300">·</span>
                        <span class="inline-flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            {{ $brd->direct_manager }}
                        </span>
                        @endif
                    </div>

                    @if($brd->status->value === 'rejected' && $brd->rejection_reason)
                    <div class="mb-5 p-3.5 rounded-lg bg-red-50 border border-red-200">
                        <h2 class="text-xs font-semibold text-red-600 uppercase tracking-wide mb-1">Rejection Reason</h2>
                        <p class="text-sm text-red-700 whitespace-pre-line">{{ $brd->rejection_reason }}</p>
                    </div>
                    @endif

                    <div>
                        <h2 class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Description</h2>
                        <p class="text-sm text-slate-700 whitespace-pre-line leading-relaxed">{{ $brd->description }}</p>
                    </div>
                </div>
            </div>

            {{-- Objective & Scope --}}
            @if($brd->objective || $brd->scope)
            <div class="bg-white rounded-xl border border-slate-200 p-6 space-y-5">
                @if($brd->objective)
                <div>
                    <h2 class="flex items-center gap-2 text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">
                        <svg class="w-4 h-4 text-[#E26B3D]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        Business Objective
                    </h2>
                    <p class="text-sm text-slate-700 whitespace-pre-line leading-relaxed">{{ $brd->objective }}</p>
                </div>
                @endif
                @if($brd->scope)
                <div class="{{ $brd->objective ? 'pt-5 border-t border-slate-100' : '' }}">
                    <h2 class="flex items-center gap-2 text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">
                        <svg class="w-4 h-4 text-[#E26B3D]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 3H5a2 2 0 00-2 2v2m18-4h-2a2 2 0 012 2v2M3 17v2a2 2 0 002 2h2m10 0h2a2 2 0 002-2v-2M12 8v8m-4-4h8"/>
                        </svg>
                        Scope
                    </h2>
                    <p class="text-sm text-slate-700 whitespace-pre-line leading-relaxed">{{ $brd->scope }}</p>
                </div>
                @endif
            </div>
            @endif

            {{-- As-Is / To-Be comparison --}}
            @if($brd->as_is_workflow || $brd->as_is_pain_points || $brd->as_is_existing_systems || $brd->to_be_workflow || $brd->to_be_benefits)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @if($brd->as_is_workflow || $brd->as_is_pain_points || $brd->as_is_existing_systems)
                <div class="bg-white rounded-xl border border-slate-200 border-l-4 border-l-slate-300 p-6 space-y-4">
                    <h2 class="flex items-center gap-2 text-xs font-semibold text-slate-500 uppercase tracking-wide">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Current Process
                        <span class="normal-case font-normal text-slate-400">("As-Is")</span>
                    </h2>
                    @if($brd->as_is_workflow)
                    <div>
                        <p class="text-xs font-medium text-slate-500 mb-1">Workflow</p>
                        <p class="text-sm text-slate-700 whitespace-pre-line leading-relaxed">{{ $brd->as_is_workflow }}</p>
                    </div>
                    @endif
                    @if($brd->as_is_pain_points)
                    <div>
                        <p class="text-xs font-medium text-slate-500 mb-1">Pain Points</p>
                        <p class="text-sm text-slate-700 whitespace-pre-line leading-relaxed">{{ $brd->as_is_pain_points }}</p>
                    </div>
                    @endif
                    @if($brd->as_is_existing_systems)
                    <div>
                        <p class="text-xs font-medium text-slate-500 mb-1">Existing Systems / Tools</p>
                        <p class="text-sm text-slate-700 whitespace-pre-line leading-relaxed">{{ $brd->as_is_existing_systems }}</p>
                    </div>
                    @endif
                </div>
                @endif

                @if($brd->to_be_workflow || $brd->to_be_benefits)
                <div class="bg-white rounded-xl border border-slate-200 border-l-4 border-l-emerald-300 p-6 space-y-4">
                    <h2 class="flex items-center gap-2 text-xs font-semibold text-slate-500 uppercase tracking-wide">
                        <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 18L9 11.25l4.306 4.306a11.95 11.95 0 015.814-5.518L21.75 6M21.75 6H16.5M21.75 6v5.25"/>
                        </svg>
                        Proposed Process
                        <span class="normal-case font-normal text-slate-400">("To-Be")</span>
                    </h2>
                    @if($brd->to_be_workflow)
                    <div>
                        <p class="text-xs font-medium text-slate-500 mb-1">Workflow</p>
                        <p class="text-sm text-slate-700 whitespace-pre-line leading-relaxed">{{ $brd->to_be_workflow }}</p>
                    </div>
                    @endif
                    @if($brd->to_be_benefits)
                    <div>
                        <p class="text-xs font-medium text-slate-500 mb-1">Benefits</p>
                        <p class="text-sm text-slate-700 whitespace-pre-line leading-relaxed">{{ $brd->to_be_benefits }}</p>
                    </div>
                    @endif
                </div>
                @endif
            </div>
            @endif

            {{-- KPIs --}}
            @if(!empty($kpiLines))
            <div class="bg-white rounded-xl border border-slate-200 p-6">
                <h2 class="flex items-center gap-2 text-xs font-semibold text-slate-500 uppercase tracking-wide mb-3">
                    <svg class="w-4 h-4 text-[#E26B3D]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    KPIs
                </h2>
                <ul class="space-y-2">
                    @foreach($kpiLines as $line)
                    <li class="flex items-start gap-2 text-sm text-slate-700">
                        <svg class="w-4 h-4 text-emerald-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>{{ $line }}</span>
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif

            {{-- Stakeholders --}}
            @if($brd->stakeholders->isNotEmpty())
            <div class="bg-white rounded-xl border border-slate-200 p-6">
                <h2 class="flex items-center gap-2 text-xs font-semibold text-slate-500 uppercase tracking-wide mb-3">
                    <svg class="w-4 h-4 text-[#E26B3D]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    Stakeholders
                    <span class="normal-case font-normal text-slate-400">({{ $brd->stakeholders->count() }})</span>
                </h2>
                <div class="divide-y divide-slate-100">
                    @foreach($brd->stakeholders as $sh)
                    <div class="flex items-start gap-3 py-3">
                        <div class="w-9 h-9 rounded-full bg-[#E26B3D]/10 flex items-center justify-center shrink-0 text-xs font-semibold text-[#E26B3D]">
                            {{ strtoupper(substr($sh->name, 0, 1)) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="text-sm font-medium text-slate-800">{{ $sh->name }}</p>
                                @if($sh->role)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs bg-blue-50 text-blue-700">{{ $sh->role }}</span>
                                @endif
                                @if($sh->department)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs bg-slate-100 text-slate-600">{{ $sh->department }}</span>
                                @endif
                            </div>
                            @if($sh->responsibility)
                            <p class="text-sm text-slate-500 mt-1">{{ $sh->responsibility }}</p>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Attachments --}}
            @if($brd->attachments->isNotEmpty())
            <div class="bg-white rounded-xl border border-slate-200 p-6">
                <h2 class="flex items-center gap-2 text-xs font-semibold text-slate-500 uppercase tracking-wide mb-3">
                    <svg class="w-4 h-4 text-[#E26B3D]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                    </svg>
                    Attachments
                    <span class="normal-case font-normal text-slate-400">({{ $brd->attachments->count() }})</span>
                </h2>
                <div class="divide-y divide-slate-100">
                    @foreach($brd->attachments as $attachment)
                    <div class="flex items-center justify-between py-3 gap-3">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-slate-800 truncate">{{ $attachment->file_name }}</p>
                            <p class="text-xs text-slate-400 truncate">
                                {{ number_format($attachment->file_size / 1024, 1) }} KB
                                @if($attachment->user) · by {{ $attachment->user->name }}@endif
                                · {{ $attachment->created_at->format('d M Y, H:i') }}
                            </p>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <a href="{{ Storage::disk('public')->url($attachment->file_path) }}" target="_blank" download
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-slate-600 hover:text-slate-800 bg-slate-100 hover:bg-slate-200 transition-colors">
                                Download
                            </a>
                            @if($canEditBrd && $brd->status->value !== 'approved')
                            <button @click="$dispatch('confirm:delete', { action: '{{ route('brds.attachments.destroy', [$brd, $attachment]) }}' })"
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
            </div>
            @endif

            {{-- Linked Tasks --}}
            @if($brd->tasks->isNotEmpty())
            <div class="bg-white rounded-xl border border-slate-200 p-6">
                <h2 class="flex items-center gap-2 text-xs font-semibold text-slate-500 uppercase tracking-wide mb-3">
                    <svg class="w-4 h-4 text-[#E26B3D]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                    Linked Tasks
                    <span class="normal-case font-normal text-slate-400">({{ $brd->tasks->count() }})</span>
                </h2>
                <div class="flex flex-wrap gap-2">
                    @foreach($brd->tasks as $linkedTask)
                        <a href="{{ route('tasks.show', $linkedTask) }}"
                           class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-slate-100 text-xs text-slate-700 hover:bg-slate-200 transition-colors">
                            <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                            </svg>
                            {{ $linkedTask->title }}
                        </a>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6 lg:sticky lg:top-20">

            @if($canDecide)
            <div class="bg-amber-50 rounded-xl border border-amber-200 p-5">
                <h2 class="flex items-center gap-2 text-sm font-semibold text-amber-800 mb-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
                    </svg>
                    Awaiting Your Decision
                </h2>
                <p class="text-xs text-amber-700 mb-4">This BRD is pending approval and needs a decision.</p>
                <div class="flex items-center gap-3">
                    <form method="POST" action="{{ route('brds.approve', $brd) }}" class="flex-1">
                        @csrf
                        <button type="submit" onclick="return confirm('Approve this BRD?')"
                                class="w-full inline-flex items-center justify-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Approve
                        </button>
                    </form>
                    <button @click="showReject = true"
                            class="flex-1 inline-flex items-center justify-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Reject
                    </button>
                </div>
            </div>
            @endif

            <div class="bg-white rounded-xl border border-slate-200 p-5">
                <h2 class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-4">Details</h2>
                <dl class="space-y-4">
                    <div>
                        <dt class="text-xs font-medium text-slate-400 mb-1">Status</dt>
                        <dd>
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium {{ $statusStyle['badge'] }}">
                                {{ ucfirst($brd->status->value) }}
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-slate-400 mb-1">Created By</dt>
                        <dd class="text-sm text-slate-700">{{ $brd->creator?->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-slate-400 mb-1">Created At</dt>
                        <dd class="text-sm text-slate-700">{{ $brd->created_at->format('d M Y, H:i') }}</dd>
                    </div>
                    @if($brd->updater && $brd->updated_at->ne($brd->created_at))
                    <div>
                        <dt class="text-xs font-medium text-slate-400 mb-1">Last Updated By</dt>
                        <dd class="text-sm text-slate-700">{{ $brd->updater->name }} <span class="text-slate-400">· {{ $brd->updated_at->format('d M Y, H:i') }}</span></dd>
                    </div>
                    @endif
                    @if($brd->approver)
                    <div class="pt-4 border-t border-slate-100">
                        <dt class="text-xs font-medium text-slate-400 mb-1">{{ $brd->status->value === 'rejected' ? 'Rejected By' : 'Approved By' }}</dt>
                        <dd class="text-sm text-slate-700">{{ $brd->approver->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-slate-400 mb-1">Decision Date</dt>
                        <dd class="text-sm text-slate-700">{{ $brd->approved_at?->format('d M Y, H:i') ?? '—' }}</dd>
                    </div>
                    @endif
                </dl>

                @if($canDeleteBrd)
                <div class="pt-4 mt-4 border-t border-slate-100">
                    <button @click="confirmDelete()" class="text-sm text-red-600 hover:text-red-700 hover:underline">
                        Delete BRD
                    </button>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Reject modal --}}
    <div x-show="showReject" x-transition.opacity class="fixed inset-0 bg-black/40 z-40" style="display:none;" @click="showReject = false"></div>
    <div x-show="showReject" x-transition class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-sm p-6" @click.outside="showReject = false">
            <h3 class="text-sm font-semibold text-slate-800 mb-3">Reject BRD</h3>
            <form method="POST" action="{{ route('brds.reject', $brd) }}">
                @csrf
                <label class="block text-xs font-medium text-slate-600 mb-1.5">Reason <span class="text-slate-400 font-normal">(optional)</span></label>
                <textarea name="rejection_reason" rows="3" placeholder="Why is this being rejected..."
                          class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#E26B3D]/40 focus:border-[#E26B3D] text-slate-700 placeholder-slate-400 resize-none transition-colors mb-4"></textarea>
                <div class="flex gap-3">
                    <button type="submit" class="flex-1 bg-red-600 hover:bg-red-700 text-white text-sm font-medium py-2.5 rounded-lg transition-colors">Reject</button>
                    <button type="button" @click="showReject = false" class="px-5 py-2.5 text-sm font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

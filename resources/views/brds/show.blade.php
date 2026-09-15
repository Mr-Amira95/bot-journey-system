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
        <a href="{{ route('brds.index', ['edit' => $brd->id]) }}"
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
    $statusColors = [
        'pending'  => 'bg-amber-100 text-amber-700',
        'approved' => 'bg-emerald-100 text-emerald-700',
        'rejected' => 'bg-red-100 text-red-700',
    ];
    $priorityColors = [
        'low'    => 'bg-slate-100 text-slate-600',
        'medium' => 'bg-blue-100 text-blue-700',
        'high'   => 'bg-red-100 text-red-700',
    ];
    $isOwner = $brd->created_by === auth()->id();
@endphp

<div x-data="{
        confirmDelete() { $dispatch('confirm:delete', { action: '{{ route('brds.destroy', $brd) }}' }); },
        showReject: false,
     }"
     class="max-w-3xl">

    <div class="bg-white rounded-xl border border-slate-200 p-6">
        <div class="flex items-start justify-between gap-4 mb-1">
            <h1 class="text-lg font-semibold text-slate-800">{{ $brd->title }}</h1>
            <div class="flex items-center gap-2 shrink-0">
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$brd->status->value] ?? 'bg-slate-100 text-slate-700' }}">
                    {{ ucfirst($brd->status->value) }}
                </span>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $priorityColors[$brd->priority->value] ?? 'bg-slate-100 text-slate-600' }}">
                    {{ ucfirst($brd->priority->value) }}
                </span>
            </div>
        </div>
        <p class="text-sm text-slate-500 mb-6">
            in <a href="{{ route('projects.show', $brd->project) }}" class="hover:text-[#E26B3D] transition-colors">{{ $brd->project?->name }}</a>
        </p>

        <div class="mb-6">
            <h2 class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Description</h2>
            <p class="text-sm text-slate-700 whitespace-pre-line">{{ $brd->description }}</p>
        </div>

        @if($brd->objective)
        <div class="mb-6">
            <h2 class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Business Objective</h2>
            <p class="text-sm text-slate-700 whitespace-pre-line">{{ $brd->objective }}</p>
        </div>
        @endif

        @if($brd->scope)
        <div class="mb-6">
            <h2 class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Scope</h2>
            <p class="text-sm text-slate-700 whitespace-pre-line">{{ $brd->scope }}</p>
        </div>
        @endif

        @if($brd->stakeholders)
        <div class="mb-6">
            <h2 class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Stakeholders</h2>
            <p class="text-sm text-slate-700 whitespace-pre-line">{{ $brd->stakeholders }}</p>
        </div>
        @endif

        @if($brd->status->value === 'rejected' && $brd->rejection_reason)
        <div class="mb-6 p-3 rounded-lg bg-red-50 border border-red-200">
            <h2 class="text-xs font-semibold text-red-600 uppercase tracking-wide mb-1">Rejection Reason</h2>
            <p class="text-sm text-red-700 whitespace-pre-line">{{ $brd->rejection_reason }}</p>
        </div>
        @endif

        <div class="grid grid-cols-2 sm:grid-cols-3 gap-x-6 gap-y-4 mb-6">
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Created By</p>
                <p class="text-sm text-slate-700">{{ $brd->creator?->name ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Created At</p>
                <p class="text-sm text-slate-700">{{ $brd->created_at->format('d M Y, H:i') }}</p>
            </div>
            @if($brd->approver)
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">{{ $brd->status->value === 'rejected' ? 'Rejected By' : 'Approved By' }}</p>
                <p class="text-sm text-slate-700">{{ $brd->approver->name }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Decision Date</p>
                <p class="text-sm text-slate-700">{{ $brd->approved_at?->format('d M Y, H:i') ?? '—' }}</p>
            </div>
            @endif
        </div>

        @if($brd->tasks->isNotEmpty())
        <div class="mb-6">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Linked Tasks</p>
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

        @if($brd->status->value === 'pending' && $canApprove && !$isOwner)
        <div class="pt-4 border-t border-slate-200 flex items-center gap-3">
            <form method="POST" action="{{ route('brds.approve', $brd) }}">
                @csrf
                <button type="submit" onclick="return confirm('Approve this BRD?')"
                        class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700 transition-colors">
                    Approve
                </button>
            </form>
            <button @click="showReject = true"
                    class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 transition-colors">
                Reject
            </button>
        </div>
        @endif

        @if($canDeleteBrd)
        <div class="pt-4 {{ $brd->status->value === 'pending' && $canApprove && !$isOwner ? '' : 'border-t border-slate-200' }} mt-4">
            <button @click="confirmDelete()" class="text-sm text-red-600 hover:text-red-700 hover:underline">
                Delete BRD
            </button>
        </div>
        @endif
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

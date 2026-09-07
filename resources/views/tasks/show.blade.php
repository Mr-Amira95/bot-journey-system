@extends('layouts.app')

@section('title', $task->title)
@section('page-title', 'Task')

@section('header-actions')
    <div class="flex items-center gap-3">
        <a href="{{ route('tasks.index', ['tab' => $viewingAll ? 'all' : 'mine']) }}"
           class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-stone-50 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back
        </a>
        @if($canEditTasks)
        <a href="{{ route('tasks.index', ['tab' => $viewingAll ? 'all' : 'mine', 'edit' => $task->id]) }}"
           class="inline-flex items-center gap-2 rounded-lg bg-[#E26B3D] px-4 py-2 text-sm font-medium text-white hover:bg-[#c85a2f] transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            Edit Task
        </a>
        @endif
    </div>
@endsection

@section('content')
@php
    $statusColors = [
        'todo'        => 'bg-slate-100 text-slate-700',
        'in_progress' => 'bg-blue-100 text-blue-700',
        'review'      => 'bg-amber-100 text-amber-700',
        'done'        => 'bg-emerald-100 text-emerald-700',
        'blocked'     => 'bg-red-100 text-red-700',
    ];
    $priorityColors = [
        'low'    => 'bg-slate-100 text-slate-600',
        'medium' => 'bg-blue-100 text-blue-700',
        'high'   => 'bg-orange-100 text-orange-700',
        'urgent' => 'bg-red-100 text-red-700',
    ];
    $isOverdue = $task->due_date && $task->due_date->isPast() && $task->status->value !== 'done';
@endphp

<div x-data="{ confirmDelete() { $dispatch('confirm:delete', { action: '{{ route('tasks.destroy', $task) }}' }); } }"
     class="max-w-3xl">

    <div class="bg-white rounded-xl border border-slate-200 p-6">
        <div class="flex items-start justify-between gap-4 mb-1">
            <h1 class="text-lg font-semibold text-slate-800">{{ $task->title }}</h1>
            <div class="flex items-center gap-2 shrink-0">
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$task->status->value] ?? 'bg-slate-100 text-slate-700' }}">
                    {{ $task->status->label() }}
                </span>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $priorityColors[$task->priority->value] ?? 'bg-slate-100 text-slate-600' }}">
                    {{ ucfirst($task->priority->value) }}
                </span>
            </div>
        </div>
        <p class="text-sm text-slate-500 mb-6">
            in <a href="{{ route('projects.show', $task->project) }}" class="hover:text-[#E26B3D] transition-colors">{{ $task->project->name }}</a>
        </p>

        @if($task->description)
            <div class="mb-6">
                <h2 class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Description</h2>
                <p class="text-sm text-slate-700 whitespace-pre-line">{{ $task->description }}</p>
            </div>
        @endif

        <div class="grid grid-cols-2 sm:grid-cols-3 gap-x-6 gap-y-4 mb-6">
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Start Date</p>
                <p class="text-sm text-slate-700">{{ $task->start_date?->format('d M Y') ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Due Date</p>
                <p class="text-sm {{ $isOverdue ? 'text-red-600 font-semibold' : 'text-slate-700' }}">
                    {{ $task->due_date?->format('d M Y') ?? '—' }}
                </p>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Estimated Hours</p>
                <p class="text-sm text-slate-700">{{ $task->estimated_hours ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Created By</p>
                <p class="text-sm text-slate-700">{{ $task->createdBy?->name ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Created At</p>
                <p class="text-sm text-slate-700">{{ $task->created_at->format('d M Y, H:i') }}</p>
            </div>
            @if($task->completed_at)
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Completed At</p>
                <p class="text-sm text-slate-700">{{ $task->completed_at->format('d M Y, H:i') }}</p>
            </div>
            @endif
        </div>

        <div class="mb-6">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Assignees</p>
            @if($task->assignees->isNotEmpty())
                <div class="flex flex-wrap gap-2">
                    @foreach($task->assignees as $assignee)
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-slate-100 text-xs text-slate-700">
                            <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            {{ $assignee->user?->name ?? 'Unknown' }}
                        </span>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-slate-400">No assignees.</p>
            @endif
        </div>

        @if($canDeleteTasks)
        <div class="pt-4 border-t border-slate-200">
            <button @click="confirmDelete()"
                    class="text-sm text-red-600 hover:text-red-700 hover:underline">
                Delete Task
            </button>
        </div>
        @endif
    </div>
</div>
@endsection

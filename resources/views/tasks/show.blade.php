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

    {{-- Attachments --}}
    <div class="bg-white rounded-xl border border-slate-200 p-6 mt-6">
        <div class="flex items-center justify-between mb-5">
            <h2 class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Attachments</h2>
            <span class="text-xs text-slate-400">{{ $task->attachments->count() }} file(s)</span>
        </div>

        @if($canEditTasks)
        <form action="{{ route('tasks.attachments.store', $task) }}" method="POST"
              enctype="multipart/form-data"
              class="mb-6 p-4 rounded-lg border border-dashed border-slate-300 bg-stone-50">
            @csrf
            <div class="flex flex-wrap gap-3 items-end">
                <div class="flex-1 min-w-48">
                    <label class="block text-xs text-slate-500 mb-1">File (photo, video, PDF, Word, Excel, etc.)</label>
                    <input type="file" name="file"
                           class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-xs text-slate-700 file:mr-2 file:py-1 file:px-2.5 file:rounded file:border-0 file:text-xs file:bg-[#E26B3D]/10 file:text-[#E26B3D] hover:file:bg-[#E26B3D]/20 focus:outline-none focus:ring-2 focus:ring-[#E26B3D]"
                           required>
                </div>
                <button type="submit"
                        class="shrink-0 rounded-lg bg-[#E26B3D] px-4 py-2 text-sm font-medium text-white hover:bg-[#c85a2f] transition-colors">
                    Upload
                </button>
            </div>
            @error('file')
                <p class="text-xs text-red-600 mt-2">{{ $message }}</p>
            @enderror
        </form>
        @endif

        @php
            $attachmentIcons = [
                'photo'      => '🖼️',
                'video'      => '🎬',
                'pdf'        => '📄',
                'word'       => '📝',
                'excel'      => '📊',
                'powerpoint' => '📽️',
                'file'       => '📎',
            ];
        @endphp

        @if($task->attachments->isNotEmpty())
        <div class="divide-y divide-slate-100">
            @foreach($task->attachments as $attachment)
            <div class="flex items-center justify-between py-3 gap-3">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center shrink-0 text-base">
                        {{ $attachmentIcons[$attachment->file_type] ?? '📎' }}
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-slate-800 truncate">{{ $attachment->file_name }}</p>
                        <p class="text-xs text-slate-400 truncate">
                            {{ number_format($attachment->file_size / 1024, 1) }} KB
                            @if($attachment->user) · by {{ $attachment->user->name }}@endif
                            · {{ $attachment->created_at->format('d M Y, H:i') }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <a href="{{ Storage::disk('public')->url($attachment->file_path) }}"
                       target="_blank" download
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-slate-600 hover:text-slate-800 bg-slate-100 hover:bg-slate-200 transition-colors">
                        Download
                    </a>
                    @if($canEditTasks)
                    <button @click="$dispatch('confirm:delete', { action: '{{ route('tasks.attachments.destroy', [$task, $attachment]) }}' })"
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
        <p class="text-sm text-slate-400 text-center py-4">No attachments yet.</p>
        @endif
    </div>

    {{-- Comments --}}
    <div class="bg-white rounded-xl border border-slate-200 p-6 mt-6">
        <div class="flex items-center justify-between mb-5">
            <h2 class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Comments</h2>
            <span class="text-xs text-slate-400">{{ $task->comments->count() }} comment(s)</span>
        </div>

        @if($canEditTasks)
        <form action="{{ route('tasks.comments.store', $task) }}" method="POST" enctype="multipart/form-data" class="mb-6">
            @csrf
            <textarea name="comment" rows="3" required placeholder="Write a comment..."
                      class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#E26B3D]"></textarea>
            @error('comment')
                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
            @enderror
            <div class="mt-2">
                <input type="file" name="attachments[]" multiple
                       class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-xs text-slate-700 file:mr-2 file:py-1 file:px-2.5 file:rounded file:border-0 file:text-xs file:bg-[#E26B3D]/10 file:text-[#E26B3D] hover:file:bg-[#E26B3D]/20 focus:outline-none focus:ring-2 focus:ring-[#E26B3D]">
            </div>
            <div class="flex justify-end mt-2">
                <button type="submit"
                        class="rounded-lg bg-[#E26B3D] px-4 py-2 text-sm font-medium text-white hover:bg-[#c85a2f] transition-colors">
                    Post Comment
                </button>
            </div>
        </form>
        @endif

        @if($task->comments->isNotEmpty())
        <div class="space-y-4">
            @foreach($task->comments as $comment)
            <div class="flex gap-3">
                <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center shrink-0 text-xs font-semibold text-slate-500">
                    {{ strtoupper(substr($comment->user?->name ?? '?', 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="rounded-lg bg-stone-50 border border-slate-200 px-3 py-2">
                        <div class="flex items-center justify-between gap-2">
                            <p class="text-sm font-medium text-slate-800">{{ $comment->user?->name ?? 'Unknown' }}</p>
                            <div class="flex items-center gap-2 shrink-0">
                                <span class="text-xs text-slate-400">{{ $comment->created_at->format('d M Y, H:i') }}</span>
                                @if($canEditTasks)
                                <button @click="$dispatch('confirm:delete', { action: '{{ route('tasks.comments.destroy', [$task, $comment]) }}' })"
                                        class="text-xs text-slate-400 hover:text-red-600">
                                    Delete
                                </button>
                                @endif
                            </div>
                        </div>
                        <p class="text-sm text-slate-700 whitespace-pre-line mt-1">{{ $comment->comment }}</p>
                        @include('tasks._attachment_list', ['attachments' => $comment->attachments])
                    </div>

                    @if($comment->replies->isNotEmpty())
                    <div class="mt-3 ml-4 space-y-3 border-l-2 border-slate-100 pl-4">
                        @foreach($comment->replies as $reply)
                        <div class="flex gap-2">
                            <div class="w-6 h-6 rounded-full bg-slate-100 flex items-center justify-center shrink-0 text-xs font-semibold text-slate-500">
                                {{ strtoupper(substr($reply->user?->name ?? '?', 0, 1)) }}
                            </div>
                            <div class="flex-1 min-w-0 rounded-lg bg-stone-50 border border-slate-200 px-3 py-2">
                                <div class="flex items-center justify-between gap-2">
                                    <p class="text-sm font-medium text-slate-800">{{ $reply->user?->name ?? 'Unknown' }}</p>
                                    <div class="flex items-center gap-2 shrink-0">
                                        <span class="text-xs text-slate-400">{{ $reply->created_at->format('d M Y, H:i') }}</span>
                                        @if($canEditTasks)
                                        <button @click="$dispatch('confirm:delete', { action: '{{ route('tasks.comments.destroy', [$task, $reply]) }}' })"
                                                class="text-xs text-slate-400 hover:text-red-600">
                                            Delete
                                        </button>
                                        @endif
                                    </div>
                                </div>
                                <p class="text-sm text-slate-700 whitespace-pre-line mt-1">{{ $reply->comment }}</p>
                                @include('tasks._attachment_list', ['attachments' => $reply->attachments])
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif

                    @if($canEditTasks)
                    <details class="mt-2 ml-1">
                        <summary class="text-xs text-slate-400 hover:text-[#E26B3D] cursor-pointer select-none">Reply</summary>
                        <form action="{{ route('tasks.comments.store', $task) }}" method="POST" enctype="multipart/form-data" class="mt-2">
                            @csrf
                            <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                            <textarea name="comment" rows="2" required placeholder="Write a reply..."
                                      class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#E26B3D]"></textarea>
                            <div class="mt-2">
                                <input type="file" name="attachments[]" multiple
                                       class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-xs text-slate-700 file:mr-2 file:py-1 file:px-2.5 file:rounded file:border-0 file:text-xs file:bg-[#E26B3D]/10 file:text-[#E26B3D] hover:file:bg-[#E26B3D]/20 focus:outline-none focus:ring-2 focus:ring-[#E26B3D]">
                            </div>
                            <div class="flex justify-end mt-2">
                                <button type="submit"
                                        class="rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-200 transition-colors">
                                    Post Reply
                                </button>
                            </div>
                        </form>
                    </details>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @else
        <p class="text-sm text-slate-400 text-center py-4">No comments yet.</p>
        @endif
    </div>
</div>
@endsection

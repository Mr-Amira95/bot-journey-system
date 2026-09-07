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
@if($attachments->isNotEmpty())
<div class="mt-2 space-y-1.5">
    @foreach($attachments as $attachment)
    <div class="flex items-center justify-between gap-2 rounded-lg bg-white border border-slate-200 px-2.5 py-1.5">
        <div class="flex items-center gap-2 min-w-0">
            <span class="text-sm shrink-0">{{ $attachmentIcons[$attachment->file_type] ?? '📎' }}</span>
            <div class="min-w-0">
                <p class="text-xs font-medium text-slate-700 truncate">{{ $attachment->file_name }}</p>
                <p class="text-[11px] text-slate-400 truncate">{{ number_format($attachment->file_size / 1024, 1) }} KB</p>
            </div>
        </div>
        <div class="flex items-center gap-1.5 shrink-0">
            <a href="{{ Storage::disk('public')->url($attachment->file_path) }}"
               target="_blank" download
               class="text-[11px] font-medium text-slate-500 hover:text-slate-700">
                Download
            </a>
            @if($canEditTasks)
            <button @click="$dispatch('confirm:delete', { action: '{{ route('tasks.attachments.destroy', [$task, $attachment]) }}' })"
                    class="text-slate-300 hover:text-red-600">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
            </button>
            @endif
        </div>
    </div>
    @endforeach
</div>
@endif

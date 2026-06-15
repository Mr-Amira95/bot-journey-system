@extends('layouts.app')

@section('title', 'Call History')
@section('page-title', 'Call History')

@section('content')
<div>
    @if($calls->isEmpty())
        <div class="text-center py-20 text-slate-400">
            <svg class="w-16 h-16 mx-auto mb-4 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
            </svg>
            <p class="text-lg font-medium text-slate-500">No calls yet</p>
            <p class="text-sm mt-1">Start a call from within a conversation</p>
        </div>
    @else
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100 bg-stone-50">
                        <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Type</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">With</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Status</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Duration</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Started by</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Time</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach($calls as $call)
                        @php
                            $isDirect = $call->conversation->type->value === 'direct';
                            $otherUser = $isDirect
                                ? $call->participants->firstWhere('user_id', '!=', auth()->id())
                                : null;
                            $displayName = $isDirect
                                ? ($otherUser?->user?->name ?? 'Unknown')
                                : ($call->conversation->title ?? 'Group Chat');
                        @endphp
                        <tr class="hover:bg-stone-50 transition-colors">
                            {{-- Type icon --}}
                            <td class="px-5 py-3.5">
                                @if($call->type->value === 'video')
                                    <span class="inline-flex items-center gap-1.5 text-purple-600 font-medium">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.069A1 1 0 0121 8.87v6.26a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                        </svg>
                                        Video
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 text-blue-600 font-medium">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                        </svg>
                                        Audio
                                    </span>
                                @endif
                            </td>

                            {{-- With --}}
                            <td class="px-5 py-3.5 font-medium text-slate-800">{{ $displayName }}</td>

                            {{-- Status badge --}}
                            <td class="px-5 py-3.5">
                                @php
                                    $statusColors = [
                                        'ringing'  => 'bg-yellow-100 text-yellow-700',
                                        'ongoing'  => 'bg-emerald-100 text-emerald-700',
                                        'ended'    => 'bg-slate-100 text-slate-600',
                                        'missed'   => 'bg-red-100 text-red-600',
                                        'rejected' => 'bg-orange-100 text-orange-600',
                                    ];
                                    $color = $statusColors[$call->status->value] ?? 'bg-slate-100 text-slate-600';
                                @endphp
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $color }}">
                                    {{ $call->status->label() }}
                                </span>
                            </td>

                            {{-- Duration --}}
                            <td class="px-5 py-3.5 text-slate-500 font-mono text-xs">
                                {{ $call->formatted_duration }}
                            </td>

                            {{-- Started by --}}
                            <td class="px-5 py-3.5 text-slate-600">
                                {{ $call->startedBy?->name ?? '—' }}
                                @if($call->started_by === auth()->id())
                                    <span class="text-xs text-slate-400">(you)</span>
                                @endif
                            </td>

                            {{-- Time --}}
                            <td class="px-5 py-3.5 text-slate-400 font-mono text-xs">
                                {{ $call->created_at->format('d M, H:i') }}
                            </td>

                            {{-- Action --}}
                            <td class="px-5 py-3.5 text-right">
                                @if(in_array($call->status->value, ['ringing', 'ongoing']))
                                    <a href="{{ route('calls.show', $call) }}"
                                       class="inline-flex items-center gap-1 text-xs font-medium text-emerald-600 hover:text-emerald-700 transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                        </svg>
                                        Rejoin
                                    </a>
                                @else
                                    <a href="{{ route('conversations.show', $call->conversation) }}"
                                       class="text-xs text-slate-400 hover:text-slate-600 transition-colors">
                                        View chat
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        </div>

        <div class="mt-4">
            {{ $calls->links() }}
        </div>
    @endif
</div>
@endsection

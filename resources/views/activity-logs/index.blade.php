@extends('layouts.app')

@section('title', 'Activity Logs')
@section('page-title', 'Activity Logs')

@section('content')
@php
    $actionColors = [
        'created' => 'bg-emerald-100 text-emerald-700',
        'updated' => 'bg-blue-100 text-blue-700',
        'deleted' => 'bg-red-100 text-red-700',
        'viewed'  => 'bg-slate-100 text-slate-600',
    ];
    $actionIcons = [
        'created' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>',
        'updated' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>',
        'deleted' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>',
        'viewed'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>',
    ];
@endphp

{{-- Filters --}}
<div class="mb-5">
    <form method="GET" action="{{ route('activity-logs.index') }}" class="flex flex-wrap items-center gap-3">
        <select name="action" class="py-2 pl-3 pr-8 rounded-lg border border-slate-300 bg-white text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#E26B3D] font-mono">
            <option value="">All Actions</option>
            @foreach($actions as $a)
                <option value="{{ $a->value }}" {{ request('action') === $a->value ? 'selected' : '' }}>{{ ucfirst($a->value) }}</option>
            @endforeach
        </select>

        <select name="module" class="py-2 pl-3 pr-8 rounded-lg border border-slate-300 bg-white text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#E26B3D] font-mono">
            <option value="">All Modules</option>
            @foreach($modules as $m)
                <option value="{{ $m }}" {{ request('module') === $m ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $m)) }}</option>
            @endforeach
        </select>

        <select name="user_id" class="py-2 pl-3 pr-8 rounded-lg border border-slate-300 bg-white text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#E26B3D] font-mono">
            <option value="">All Users</option>
            @foreach($users as $u)
                <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
            @endforeach
        </select>

        <input type="date" name="date_from" value="{{ request('date_from') }}"
               class="py-2 px-3 rounded-lg border border-slate-300 bg-white text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#E26B3D] font-mono">
        <input type="date" name="date_to" value="{{ request('date_to') }}"
               class="py-2 px-3 rounded-lg border border-slate-300 bg-white text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#E26B3D] font-mono">

        <button type="submit" class="px-4 py-2 rounded-lg bg-white border border-slate-300 text-sm text-slate-700 hover:bg-stone-50 transition-colors font-mono">Filter</button>
        @if(request()->hasAny(['action', 'module', 'user_id', 'date_from', 'date_to']))
            <a href="{{ route('activity-logs.index') }}" class="text-sm text-slate-500 hover:text-slate-700 font-mono">Clear</a>
        @endif
    </form>
</div>

{{-- Table --}}
<div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
    <div class="overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-stone-50 border-b border-slate-200">
            <tr>
                <th class="text-left px-5 py-3.5 text-xs font-mono font-medium text-slate-500 uppercase tracking-wider">Action</th>
                <th class="text-left px-5 py-3.5 text-xs font-mono font-medium text-slate-500 uppercase tracking-wider">Module</th>
                <th class="text-left px-5 py-3.5 text-xs font-mono font-medium text-slate-500 uppercase tracking-wider">Description</th>
                <th class="text-left px-5 py-3.5 text-xs font-mono font-medium text-slate-500 uppercase tracking-wider">User</th>
                <th class="text-left px-5 py-3.5 text-xs font-mono font-medium text-slate-500 uppercase tracking-wider">IP Address</th>
                <th class="text-left px-5 py-3.5 text-xs font-mono font-medium text-slate-500 uppercase tracking-wider">Timestamp</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($logs as $log)
                @php
                    $ac = $actionColors[$log->action->value] ?? 'bg-slate-100 text-slate-600';
                    $ai = $actionIcons[$log->action->value] ?? '';
                @endphp
                <tr class="hover:bg-stone-50/60 transition-colors">
                    <td class="px-5 py-4">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-mono font-medium {{ $ac }}">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $ai !!}</svg>
                            {{ ucfirst($log->action->value) }}
                        </span>
                    </td>
                    <td class="px-5 py-4">
                        <span class="text-slate-700 font-mono text-xs bg-slate-100 px-2 py-0.5 rounded">
                            {{ ucfirst(str_replace('_', ' ', $log->module)) }}
                        </span>
                    </td>
                    <td class="px-5 py-4 text-slate-700 max-w-xs">
                        <div @if($log->old_values || $log->new_values) x-data="{ open: false }" @endif>
                            <span class="line-clamp-2">{{ $log->description ?? '—' }}</span>
                            @if($log->old_values || $log->new_values)
                                <button @click="open = !open"
                                        class="mt-1 text-xs text-[#E26B3D] hover:underline font-mono">
                                    <span x-text="open ? 'Hide changes' : 'View changes'">View changes</span>
                                </button>
                                <div x-show="open" class="mt-2 text-xs font-mono space-y-1" style="display:none;">
                                    @if($log->old_values)
                                        <div class="bg-red-50 rounded p-2 text-red-700 break-all">
                                            <span class="font-semibold">Before:</span> {{ json_encode($log->old_values, JSON_PRETTY_PRINT) }}
                                        </div>
                                    @endif
                                    @if($log->new_values)
                                        <div class="bg-emerald-50 rounded p-2 text-emerald-700 break-all">
                                            <span class="font-semibold">After:</span> {{ json_encode($log->new_values, JSON_PRETTY_PRINT) }}
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </td>
                    <td class="px-5 py-4">
                        @if($log->user)
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded-full bg-[#E26B3D] flex items-center justify-center text-white text-xs font-semibold shrink-0">
                                    {{ strtoupper(substr($log->user->name, 0, 1)) }}
                                </div>
                                <span class="text-slate-700 text-sm">{{ $log->user->name }}</span>
                            </div>
                        @else
                            <span class="text-slate-400 font-mono text-xs">System</span>
                        @endif
                    </td>
                    <td class="px-5 py-4 font-mono text-xs text-slate-500">{{ $log->ip_address ?? '—' }}</td>
                    <td class="px-5 py-4 font-mono text-xs text-slate-500 whitespace-nowrap">
                        <span title="{{ $log->created_at->format('Y-m-d H:i:s') }}">
                            {{ $log->created_at->format('M d, Y') }}<br>
                            <span class="text-slate-400">{{ $log->created_at->format('H:i:s') }}</span>
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-5 py-14 text-center">
                        <p class="text-slate-400 font-mono text-sm">No activity logs found.</p>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
    </div>
    @if($logs->hasPages())
        <div class="px-5 py-3 border-t border-slate-100">{{ $logs->links() }}</div>
    @endif
</div>
@endsection

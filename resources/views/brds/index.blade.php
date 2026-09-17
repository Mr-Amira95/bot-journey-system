@extends('layouts.app')

@section('title', 'BRDs')
@section('page-title', 'Business Requirements Documents')

@section('header-actions')
    @if(auth()->user()->hasPermission('create_brds'))
    <a href="{{ route('brds.create') }}"
       class="inline-flex items-center gap-2 rounded-lg bg-[#E26B3D] px-4 py-2 text-sm font-mono font-medium text-white hover:bg-[#c8602a] transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        New BRD
    </a>
    @endif
@endsection

@section('content')
@if($canViewAll)
<div class="flex items-center gap-1 bg-slate-100 rounded-xl p-1 w-fit mb-5">
    <a href="{{ route('brds.index', ['tab' => 'mine']) }}"
       class="px-4 py-2 rounded-lg text-sm font-medium transition-all {{ $tab === 'mine' ? 'bg-white shadow text-slate-800' : 'text-slate-500 hover:text-slate-700' }}">
        My BRDs
    </a>
    <a href="{{ route('brds.index', ['tab' => 'all']) }}"
       class="px-4 py-2 rounded-lg text-sm font-medium transition-all {{ $tab === 'all' ? 'bg-white shadow text-slate-800' : 'text-slate-500 hover:text-slate-700' }}">
        All BRDs
    </a>
</div>
@endif

<div>
    {{-- Filters --}}
    <div class="mb-5">
        <form method="GET" action="{{ route('brds.index') }}" class="flex flex-wrap items-center gap-3">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <select name="project_id" class="py-2 pl-3 pr-8 rounded-lg border border-slate-300 bg-white text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#E26B3D] font-mono">
                <option value="">All Projects</option>
                @foreach($projects as $proj)
                    <option value="{{ $proj->id }}" {{ request('project_id') == $proj->id ? 'selected' : '' }}>{{ $proj->name }}</option>
                @endforeach
            </select>
            <select name="status" class="py-2 pl-3 pr-8 rounded-lg border border-slate-300 bg-white text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#E26B3D] font-mono">
                <option value="">All Statuses</option>
                @foreach($statuses as $s)
                    <option value="{{ $s->value }}" {{ request('status') === $s->value ? 'selected' : '' }}>{{ ucfirst($s->value) }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 rounded-lg bg-white border border-slate-300 text-sm text-slate-700 hover:bg-stone-50 transition-colors font-mono">Filter</button>
            @if(request()->hasAny(['project_id', 'status']))
                <a href="{{ route('brds.index', ['tab' => $tab]) }}" class="text-sm text-slate-500 hover:text-slate-700 font-mono">Clear</a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-stone-50 border-b border-slate-200">
                <tr>
                    <th class="text-left px-5 py-3.5 text-xs font-mono font-medium text-slate-500 uppercase tracking-wider">Title</th>
                    <th class="text-left px-5 py-3.5 text-xs font-mono font-medium text-slate-500 uppercase tracking-wider">Project</th>
                    @if($tab === 'all')<th class="text-left px-5 py-3.5 text-xs font-mono font-medium text-slate-500 uppercase tracking-wider">Created By</th>@endif
                    <th class="text-left px-5 py-3.5 text-xs font-mono font-medium text-slate-500 uppercase tracking-wider">Priority</th>
                    <th class="text-left px-5 py-3.5 text-xs font-mono font-medium text-slate-500 uppercase tracking-wider">Status</th>
                    <th class="text-right px-5 py-3.5 text-xs font-mono font-medium text-slate-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @php
                    $statusColors = [
                        'pending'   => 'bg-amber-100 text-amber-700',
                        'approved'  => 'bg-emerald-100 text-emerald-700',
                        'rejected'  => 'bg-red-100 text-red-700',
                    ];
                    $priorityColors = [
                        'low'    => 'bg-slate-100 text-slate-600',
                        'medium' => 'bg-blue-100 text-blue-700',
                        'high'   => 'bg-red-100 text-red-700',
                    ];
                    $canDeleteBrd = auth()->user()->hasPermission('delete_brds');
                @endphp
                @forelse($brds as $brd)
                    @php
                        $sc = $statusColors[$brd->status->value] ?? 'bg-slate-100 text-slate-600';
                        $pc = $priorityColors[$brd->priority->value] ?? 'bg-slate-100 text-slate-600';
                        $isOwner = $brd->created_by === auth()->id();
                    @endphp
                    <tr class="hover:bg-stone-50/60 transition-colors">
                        <td class="px-5 py-4 font-medium text-slate-800">
                            <a href="{{ route('brds.show', $brd) }}" class="hover:text-[#E26B3D] transition-colors">{{ $brd->title }}</a>
                        </td>
                        <td class="px-5 py-4 text-slate-700">{{ $brd->project?->name ?? '—' }}</td>
                        @if($tab === 'all')
                        <td class="px-5 py-4 text-slate-700">{{ $brd->creator?->name ?? '—' }}</td>
                        @endif
                        <td class="px-5 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-mono font-medium {{ $pc }}">
                                {{ ucfirst($brd->priority->value) }}
                            </span>
                        </td>
                        <td class="px-5 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-mono font-medium {{ $sc }}">
                                {{ ucfirst($brd->status->value) }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-right">
                            <div class="inline-flex items-center gap-1.5">
                                @if($brd->status->value === 'pending' && $canApprove && !$isOwner)
                                    <form method="POST" action="{{ route('brds.approve', $brd) }}" class="inline">
                                        @csrf
                                        <button type="submit" onclick="return confirm('Approve this BRD?')"
                                                class="p-1.5 rounded-lg text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 transition-colors" title="Approve">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </button>
                                    </form>
                                    <button @click="$dispatch('brd:reject', { action: '{{ route('brds.reject', $brd) }}' })"
                                            class="p-1.5 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 transition-colors" title="Reject">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                @endif
                                @if($canExport)
                                <a href="{{ route('brds.export', $brd) }}"
                                   class="p-1.5 rounded-lg text-slate-400 hover:text-[#0f1b3d] hover:bg-slate-100 transition-colors" title="Export PDF">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                    </svg>
                                </a>
                                @endif
                                @if($canEditBrd && $brd->status->value !== 'approved')
                                <a href="{{ route('brds.edit', $brd) }}"
                                   class="p-1.5 rounded-lg text-slate-400 hover:text-[#E26B3D] hover:bg-[#E26B3D]/10 transition-colors" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                @endif
                                @if($canDeleteBrd)
                                <button @click="$dispatch('confirm:delete', { action: '{{ route('brds.destroy', $brd) }}' })"
                                        class="p-1.5 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $tab === 'all' ? 6 : 5 }}" class="px-5 py-14 text-center">
                            <p class="text-slate-400 font-mono text-sm">No BRDs found.</p>
                            @if(auth()->user()->hasPermission('create_brds'))
                            <a href="{{ route('brds.create') }}" class="mt-3 inline-block text-sm text-[#E26B3D] hover:underline font-mono">Create the first one</a>
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
        @if($brds->hasPages())
            <div class="px-5 py-3 border-t border-slate-100">{{ $brds->links() }}</div>
        @endif
    </div>

    {{-- Reject reason modal --}}
    <div x-data="{ show: false, action: '' }" @brd:reject.window="action = $event.detail.action; show = true">
        <div x-show="show" x-transition.opacity class="fixed inset-0 bg-black/40 z-40" style="display:none;" @click="show = false"></div>
        <div x-show="show" x-transition class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-sm p-6" @click.outside="show = false">
                <h3 class="text-sm font-semibold text-slate-800 mb-3">Reject BRD</h3>
                <form method="POST" :action="action">
                    @csrf
                    <label class="block text-xs font-medium text-slate-600 mb-1.5">Reason <span class="text-slate-400 font-normal">(optional)</span></label>
                    <textarea name="rejection_reason" rows="3" placeholder="Why is this being rejected..."
                              class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#E26B3D]/40 focus:border-[#E26B3D] text-slate-700 placeholder-slate-400 resize-none transition-colors mb-4"></textarea>
                    <div class="flex gap-3">
                        <button type="submit" class="flex-1 bg-red-600 hover:bg-red-700 text-white text-sm font-medium py-2.5 rounded-lg transition-colors font-mono">Reject</button>
                        <button type="button" @click="show = false" class="px-5 py-2.5 text-sm font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors font-mono">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

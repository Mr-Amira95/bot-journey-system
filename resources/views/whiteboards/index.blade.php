@extends('layouts.app')

@section('title', 'Whiteboards')
@section('page-title', 'Whiteboards')

@section('content')
<div x-data="{ showNew: {{ $errors->any() ? 'true' : 'false' }} }">

    {{-- Tabs + New Board button --}}
    <div class="flex items-center justify-between mb-5 gap-4 flex-wrap">
        <div class="flex items-center gap-1 bg-slate-100 rounded-xl p-1">
            <a href="{{ route('whiteboards.index', ['tab' => 'mine']) }}"
               class="px-4 py-2 rounded-lg text-sm font-medium transition-all {{ $tab === 'mine' ? 'bg-white shadow text-slate-800' : 'text-slate-500 hover:text-slate-700' }}">
                My Boards
                <span class="ml-1 text-xs font-mono opacity-60">{{ $myCount }}</span>
            </a>
            <a href="{{ route('whiteboards.index', ['tab' => 'shared']) }}"
               class="px-4 py-2 rounded-lg text-sm font-medium transition-all {{ $tab === 'shared' ? 'bg-white shadow text-slate-800' : 'text-slate-500 hover:text-slate-700' }}">
                Shared with me
                <span class="ml-1 text-xs font-mono opacity-60">{{ $sharedCount }}</span>
            </a>
            @if($viewingAll)
            <a href="{{ route('whiteboards.index', ['tab' => 'all']) }}"
               class="px-4 py-2 rounded-lg text-sm font-medium transition-all flex items-center gap-1.5 {{ $tab === 'all' ? 'bg-white shadow text-slate-800' : 'text-slate-500 hover:text-slate-700' }}">
                <svg class="w-3.5 h-3.5 {{ $tab === 'all' ? 'text-[#E26B3D]' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
                All Boards
                <span class="ml-1 text-xs font-mono opacity-60">{{ $allCount }}</span>
            </a>
            @endif
        </div>

        @if(auth()->user()->hasPermission('create_whiteboards'))
        <button @click="showNew = true"
                class="inline-flex items-center gap-2 px-4 py-2 bg-[#E26B3D] text-white text-sm font-medium rounded-lg hover:bg-[#c85a2f] transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            New Board
        </button>
        @endif
    </div>

    {{-- Filter bar --}}
    <form method="GET" action="{{ route('whiteboards.index') }}" class="flex flex-wrap items-center gap-3 mb-5">
        <input type="hidden" name="tab" value="{{ $tab }}">
        <div class="relative flex-1 min-w-[200px] max-w-sm">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Search boards…"
                   class="w-full pl-9 pr-4 py-2 rounded-lg border border-slate-300 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-[#E26B3D]/40 focus:border-[#E26B3D]">
        </div>
        @if($tab === 'all')
        <select name="owner" class="py-2 pl-3 pr-8 rounded-lg border border-slate-300 bg-white text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#E26B3D]/40 focus:border-[#E26B3D]">
            <option value="">All Owners</option>
            @foreach($users as $u)
                <option value="{{ $u->id }}" {{ request('owner') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
            @endforeach
        </select>
        @endif
        <button type="submit" class="px-4 py-2 rounded-lg bg-white border border-slate-300 text-sm text-slate-700 hover:bg-stone-50 transition-colors">Filter</button>
        @if(request()->hasAny(['search', 'owner']))
            <a href="{{ route('whiteboards.index', ['tab' => $tab]) }}" class="text-sm text-slate-500 hover:text-slate-700">Clear</a>
        @endif
    </form>

    {{-- Board grid --}}
    @if($boards->isEmpty())
        <div class="py-20 text-center">
            <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-slate-100 flex items-center justify-center">
                <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                </svg>
            </div>
            @if($tab === 'mine')
                <p class="text-slate-500 text-sm">You haven't created any boards yet.</p>
                @if(auth()->user()->hasPermission('create_whiteboards'))
                <button @click="showNew = true" class="mt-3 text-sm text-[#E26B3D] hover:underline">Create your first board →</button>
                @endif
            @elseif($tab === 'shared')
                <p class="text-slate-500 text-sm">No boards have been shared with you yet.</p>
            @else
                <p class="text-slate-500 text-sm">No whiteboards found{{ request('search') ? ' for "'.request('search').'"' : '' }}.</p>
            @endif
        </div>
    @else
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
            @foreach($boards as $board)
                @php $isOwner = $board->user_id === auth()->id(); @endphp
                <div class="group bg-white rounded-xl border overflow-hidden hover:shadow-md transition-all
                    {{ $tab === 'all' && !$isOwner ? 'border-amber-200 hover:border-amber-400' : 'border-slate-200 hover:border-[#E26B3D]/50' }}">

                    <a href="{{ route('whiteboards.show', $board) }}" class="block aspect-video overflow-hidden bg-slate-50">
                        @if($board->thumbnail_url)
                            <img src="{{ $board->thumbnail_url }}?v={{ $board->updated_at->timestamp }}"
                                 alt="{{ $board->title }}"
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                        @else
                            <div class="w-full h-full flex flex-col items-center justify-center gap-2
                                {{ $tab === 'mine' ? 'bg-gradient-to-br from-[#0f1b3d] to-[#1e3a6e]' : ($tab === 'all' && !$isOwner ? 'bg-gradient-to-br from-amber-900 to-amber-700' : 'bg-gradient-to-br from-slate-700 to-slate-800') }}">
                                <svg class="w-10 h-10 text-white/20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                </svg>
                                <span class="text-xs text-white/30 font-mono">No saves yet</span>
                            </div>
                        @endif
                    </a>

                    <div x-data="{
                        renaming: false,
                        boardTitle: {{ json_encode($board->title) }},
                        startRename() { this.renaming = true; this.$nextTick(() => this.$refs.renameInput.select()); },
                        async saveRename() {
                            const val = this.$refs.renameInput.value.trim();
                            if (!val) { this.renaming = false; return; }
                            try {
                                const r = await fetch('{{ route('whiteboards.rename', $board) }}', {
                                    method: 'PATCH',
                                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                                    body: JSON.stringify({ title: val })
                                });
                                const d = await r.json();
                                if (d.success) this.boardTitle = d.title;
                            } catch(e) {}
                            this.renaming = false;
                        },
                        cancelRename() { this.renaming = false; }
                    }" class="p-3">
                        @if($isOwner)
                        <p x-show="!renaming"
                           @click="startRename()"
                           class="text-sm font-medium text-slate-800 truncate cursor-pointer hover:text-[#E26B3D] transition-colors"
                           title="Click to rename"
                           x-text="boardTitle"></p>
                        <input x-show="renaming"
                               x-ref="renameInput"
                               :value="boardTitle"
                               @keydown.enter.prevent="saveRename()"
                               @keydown.escape.prevent="cancelRename()"
                               @blur="saveRename()"
                               class="text-sm font-medium text-slate-800 border-b border-[#E26B3D] outline-none bg-transparent w-full py-0.5">
                        @else
                        <p class="text-sm font-medium text-slate-800 truncate">{{ $board->title }}</p>
                        @endif

                        @if($tab !== 'mine' && $board->user)
                            <div class="flex items-center gap-1.5 mt-0.5">
                                <div class="w-4 h-4 rounded-full bg-[#E26B3D] flex items-center justify-center text-white font-bold shrink-0" style="font-size:9px">
                                    {{ strtoupper(substr($board->user->name, 0, 1)) }}
                                </div>
                                <p class="text-xs text-slate-500 truncate">{{ $board->user->name }}</p>
                            </div>
                        @endif

                        <p class="text-xs text-slate-400 mt-0.5 font-mono">{{ $board->updated_at->diffForHumans() }}</p>

                        @if($isOwner && ($board->shares_count ?? 0) > 0)
                            <p class="text-xs text-slate-400 mt-0.5">Shared with {{ $board->shares_count }} {{ Str::plural('person', $board->shares_count) }}</p>
                        @endif

                        <div class="flex items-center gap-2 mt-2.5">
                            <a href="{{ route('whiteboards.show', $board) }}"
                               class="flex-1 text-center py-1.5 text-xs border rounded-lg font-medium transition-colors
                                   {{ $isOwner ? 'text-[#E26B3D] border-[#E26B3D]/40 hover:bg-[#E26B3D] hover:text-white' : 'text-slate-600 border-slate-300 hover:bg-slate-50' }}">
                                {{ $isOwner ? 'Open' : 'View' }}
                            </a>
                            @if($isOwner && auth()->user()->hasPermission('delete_whiteboards'))
                            <button @click="$dispatch('confirm:delete', { action: '{{ route('whiteboards.destroy', $board) }}' })"
                                    class="p-1.5 text-slate-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- New board modal --}}
    <div x-show="showNew"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4"
         @click.self="showNew = false">

        <div x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6">

            <div class="flex items-center justify-between mb-4">
                <h2 class="text-base font-semibold text-slate-800">New Whiteboard</h2>
                <button @click="showNew = false" class="text-slate-400 hover:text-slate-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form method="POST" action="{{ route('whiteboards.store') }}">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Title</label>
                    <input type="text" name="title" value="{{ old('title') }}"
                           placeholder="e.g. Q3 Planning Board (leave empty for auto-name)"
                           autofocus
                           class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#E26B3D]/30 focus:border-[#E26B3D]">
                    @error('title')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div class="flex gap-3">
                    <button type="button" @click="showNew = false"
                            class="flex-1 px-4 py-2 text-sm font-medium text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                            class="flex-1 px-4 py-2 text-sm font-medium text-white bg-[#E26B3D] hover:bg-[#c85a2f] rounded-lg transition-colors">
                        Create
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

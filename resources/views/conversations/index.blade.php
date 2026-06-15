@extends('layouts.app')

@section('title', 'Conversations')
@section('page-title', 'Conversations')

@section('header-actions')
    @if(auth()->user()->hasPermission('create_conversations'))
    <button @click="$dispatch('open-new-chat')"
            class="inline-flex items-center gap-2 bg-[#E26B3D] hover:bg-[#c95a2f] text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        New Chat
    </button>
    @endif
@endsection

@section('content')
{{-- Tabs --}}
@if($viewingAll)
<div class="flex items-center gap-1 bg-slate-100 rounded-xl p-1 w-fit mb-5">
    <a href="{{ route('conversations.index', ['tab' => 'mine']) }}"
       class="px-4 py-2 rounded-lg text-sm font-medium transition-all {{ $tab === 'mine' ? 'bg-white shadow text-slate-800' : 'text-slate-500 hover:text-slate-700' }}">
        My Messages
    </a>
    <a href="{{ route('conversations.index', ['tab' => 'all']) }}"
       class="px-4 py-2 rounded-lg text-sm font-medium transition-all flex items-center gap-1.5 {{ $tab === 'all' ? 'bg-white shadow text-slate-800' : 'text-slate-500 hover:text-slate-700' }}">
        <svg class="w-3.5 h-3.5 {{ $tab === 'all' ? 'text-[#E26B3D]' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
        </svg>
        All Messages
    </a>
</div>
@endif

{{-- Filter bar --}}
<form method="GET" action="{{ route('conversations.index') }}" class="flex flex-wrap items-center gap-3 mb-5">
    <input type="hidden" name="tab" value="{{ $tab }}">
    <div class="relative flex-1 min-w-[200px] max-w-sm">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Search conversations…"
               class="w-full pl-9 pr-4 py-2 rounded-lg border border-slate-300 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-[#E26B3D]/40 focus:border-[#E26B3D]">
    </div>
    <select name="type" class="py-2 pl-3 pr-8 rounded-lg border border-slate-300 bg-white text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#E26B3D]/40 focus:border-[#E26B3D]">
        <option value="">All Types</option>
        <option value="direct" {{ request('type') === 'direct' ? 'selected' : '' }}>Direct</option>
        <option value="group" {{ request('type') === 'group' ? 'selected' : '' }}>Group</option>
    </select>
    @if($tab === 'all')
    <select name="participant" class="py-2 pl-3 pr-8 rounded-lg border border-slate-300 bg-white text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#E26B3D]/40 focus:border-[#E26B3D]">
        <option value="">Any Participant</option>
        @foreach($users as $u)
            <option value="{{ $u->id }}" {{ request('participant') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
        @endforeach
    </select>
    @endif
    <button type="submit" class="px-4 py-2 rounded-lg bg-white border border-slate-300 text-sm text-slate-700 hover:bg-stone-50 transition-colors">Filter</button>
    @if(request()->hasAny(['search', 'type', 'participant']))
        <a href="{{ route('conversations.index', ['tab' => $tab]) }}" class="text-sm text-slate-500 hover:text-slate-700">Clear</a>
    @endif
</form>

<div x-data="newChatForm()" @open-new-chat.window="showNew = true">

    {{-- Conversation list --}}
    @if($conversations->isEmpty())
        <div class="text-center py-20 text-slate-400">
            <svg class="w-16 h-16 mx-auto mb-4 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
            </svg>
            <p class="text-lg font-medium text-slate-500">No conversations yet</p>
            <p class="text-sm mt-1">Start a new chat to get going</p>
        </div>
    @else
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 divide-y divide-slate-100 overflow-hidden">
            @foreach($conversations as $conv)
                @php
                    $authId = auth()->id();
                    $isDirect = $conv->type->value === 'direct';
                    $otherUser = $isDirect
                        ? $conv->users->firstWhere('id', '!=', $authId)
                        : null;
                    $displayName = $isDirect
                        ? ($otherUser?->name ?? 'Unknown')
                        : ($conv->title ?? 'Group Chat');
                    $initial = strtoupper(substr($displayName, 0, 1));
                    $lastMsg = $conv->latestMessage;
                @endphp
                <a href="{{ route('conversations.show', $conv) }}"
                   class="flex items-center gap-4 px-5 py-4 hover:bg-stone-50 transition-colors group">

                    {{-- Avatar --}}
                    <div class="w-12 h-12 rounded-full flex items-center justify-center shrink-0 text-white font-semibold text-lg
                        {{ $isDirect ? 'bg-[#E26B3D]' : 'bg-[#0f1b3d]' }}">
                        @if($isDirect)
                            {{ $initial }}
                        @else
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        @endif
                    </div>

                    {{-- Info --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-2">
                            <p class="font-semibold text-slate-800 truncate">{{ $displayName }}</p>
                            <div class="flex items-center gap-2 shrink-0">
                                @if($conv->unread_count > 0)
                                    <span class="bg-[#E26B3D] text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center">
                                        {{ $conv->unread_count > 9 ? '9+' : $conv->unread_count }}
                                    </span>
                                @endif
                                @if($lastMsg)
                                    <span class="text-xs text-slate-400 font-mono">
                                        {{ $lastMsg->created_at->isToday() ? $lastMsg->created_at->format('H:i') : $lastMsg->created_at->format('d M') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="flex items-center gap-1 mt-0.5">
                            @if(!$isDirect)
                                <span class="text-xs text-slate-400">{{ $conv->users->count() }} members ·</span>
                            @endif
                            @if($viewingAll && $isDirect)
                                <span class="text-xs text-amber-600 font-medium shrink-0">
                                    {{ $conv->users->pluck('name')->join(' ↔ ') }} ·
                                </span>
                            @endif
                            @if($lastMsg)
                                <p class="text-sm text-slate-500 truncate">
                                    @if($lastMsg->user_id === auth()->id())
                                        <span class="text-slate-400">You: </span>
                                    @endif
                                    {{ Str::limit($lastMsg->body, 60) }}
                                </p>
                            @else
                                <p class="text-sm text-slate-400 italic">No messages yet</p>
                            @endif
                        </div>
                    </div>

                    <svg class="w-4 h-4 text-slate-300 group-hover:text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            @endforeach
        </div>
    @endif

    {{-- New Chat — Backdrop --}}
    <div x-show="showNew"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-black/40 z-40"
         style="display:none;"
         @click="close()"></div>

    {{-- New Chat — Side Panel --}}
    <div x-show="showNew"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="translate-x-full"
         x-transition:enter-end="translate-x-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="translate-x-0"
         x-transition:leave-end="translate-x-full"
         class="fixed top-0 right-0 h-full w-96 bg-white shadow-2xl z-50 flex flex-col"
         style="display:none;">

        {{-- Header --}}
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100 shrink-0">
            <h2 class="text-base font-semibold text-slate-800">New Conversation</h2>
            <button @click="close()" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-stone-100 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <form method="POST" action="{{ route('conversations.store') }}" class="flex flex-col flex-1 overflow-hidden">
            @csrf
            <input type="hidden" name="type" :value="chatType">
            <template x-for="id in selectedIds" :key="id">
                <input type="hidden" name="user_ids[]" :value="id">
            </template>

            <div class="flex-1 overflow-y-auto px-5 py-4 space-y-4">

                {{-- Type tabs --}}
                <div class="flex rounded-lg bg-slate-100 p-1">
                    <button type="button"
                            @click="chatType = 'direct'; selectedIds = selectedIds.slice(0, 1)"
                            :class="chatType === 'direct' ? 'bg-white shadow text-slate-800' : 'text-slate-500 hover:text-slate-700'"
                            class="flex-1 py-2 text-sm font-medium rounded-md transition-all">
                        Direct Message
                    </button>
                    <button type="button"
                            @click="chatType = 'group'"
                            :class="chatType === 'group' ? 'bg-white shadow text-slate-800' : 'text-slate-500 hover:text-slate-700'"
                            class="flex-1 py-2 text-sm font-medium rounded-md transition-all">
                        Group Chat
                    </button>
                </div>

                {{-- Group title --}}
                <div x-show="chatType === 'group'" x-cloak>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Group Name <span class="text-red-500">*</span></label>
                    <input type="text" name="title" value="{{ old('title') }}"
                           placeholder="e.g. Dev Team"
                           class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#E26B3D]/30 focus:border-[#E26B3D]">
                    @error('title')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Group description --}}
                <div x-show="chatType === 'group'" x-cloak>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Description</label>
                    <input type="text" name="description" value="{{ old('description') }}"
                           placeholder="What's this group about?"
                           class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#E26B3D]/30 focus:border-[#E26B3D]">
                </div>

                {{-- Members --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">
                        <span x-show="chatType === 'direct'">Select Person <span class="text-red-500">*</span></span>
                        <span x-show="chatType === 'group'" x-cloak>Add Members <span class="text-red-500">*</span></span>
                    </label>

                    {{-- Search input --}}
                    <div class="relative mb-2">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input type="text" x-model="search" placeholder="Search people…"
                               class="w-full pl-9 pr-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#E26B3D]/30 focus:border-[#E26B3D]">
                    </div>

                    {{-- Selected badges (group) --}}
                    <div x-show="chatType === 'group' && selectedIds.length > 0" x-cloak class="flex flex-wrap gap-1.5 mb-2">
                        <template x-for="id in selectedIds" :key="id">
                            <span class="inline-flex items-center gap-1 bg-[#E26B3D]/10 text-[#E26B3D] text-xs font-medium px-2 py-1 rounded-full">
                                <span x-text="users.find(u => u.id === id)?.name"></span>
                                <button type="button" @click="toggleUser(id)" class="hover:text-[#c95a2f] leading-none">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </span>
                        </template>
                    </div>

                    {{-- User list --}}
                    <div class="border border-slate-200 rounded-lg overflow-hidden divide-y divide-slate-100">
                        <template x-if="filteredUsers.length === 0">
                            <div class="px-4 py-6 text-center text-sm text-slate-400">No people found</div>
                        </template>
                        <template x-for="user in filteredUsers" :key="user.id">
                            <button type="button"
                                    @click="toggleUser(user.id)"
                                    class="w-full flex items-center gap-3 px-4 py-3 hover:bg-stone-50 transition-colors text-left"
                                    :class="isSelected(user.id) ? 'bg-[#E26B3D]/5' : ''">
                                <div class="w-8 h-8 rounded-full bg-[#E26B3D] flex items-center justify-center text-white text-sm font-semibold shrink-0"
                                     x-text="user.name[0]?.toUpperCase()"></div>
                                <span class="flex-1 text-sm text-slate-800" x-text="user.name"></span>
                                <svg x-show="isSelected(user.id)" class="w-4 h-4 text-[#E26B3D] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display:none;">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                </svg>
                            </button>
                        </template>
                    </div>

                    <p x-show="chatType === 'group'" x-cloak class="text-xs text-slate-400 mt-1">Click to select multiple members</p>

                    @error('user_ids')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

            </div>

            {{-- Footer --}}
            <div class="px-5 py-4 border-t border-slate-100 flex gap-3 shrink-0">
                <button type="button" @click="close()"
                        class="flex-1 px-4 py-2 text-sm font-medium text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors">
                    Cancel
                </button>
                <button type="submit"
                        class="flex-1 px-4 py-2 text-sm font-medium text-white bg-[#E26B3D] hover:bg-[#c95a2f] rounded-lg transition-colors">
                    Start Chat
                </button>
            </div>
        </form>
    </div>

</div>

<script>
document.addEventListener('alpine:init', function () {
    Alpine.data('newChatForm', function () {
        return {
            showNew: {{ $errors->any() ? 'true' : 'false' }},
            chatType: '{{ old('type', 'direct') }}',
            search: '',
            selectedIds: @json(collect(old('user_ids', []))->map(fn($id) => (int) $id)->values()),
            users: @json($users->map(fn($u) => ['id' => $u->id, 'name' => $u->name])->values()),
            get filteredUsers() {
                const q = this.search.toLowerCase();
                return q ? this.users.filter(u => u.name.toLowerCase().includes(q)) : this.users;
            },
            toggleUser(id) {
                if (this.chatType === 'direct') {
                    this.selectedIds = (this.selectedIds.length === 1 && this.selectedIds[0] === id) ? [] : [id];
                } else {
                    const idx = this.selectedIds.indexOf(id);
                    if (idx === -1) this.selectedIds.push(id);
                    else this.selectedIds.splice(idx, 1);
                }
            },
            isSelected(id) { return this.selectedIds.includes(id); },
            close() { this.showNew = false; this.chatType = 'direct'; this.search = ''; this.selectedIds = []; },
        };
    });
});
</script>
@endsection

@extends('layouts.app')

@section('title', $conversation->type->value === 'direct'
    ? ($conversation->users->firstWhere('id', '!=', auth()->id())?->name ?? 'Chat')
    : ($conversation->title ?? 'Group Chat'))

@section('page-title')
    @php
        $isDirect = $conversation->type->value === 'direct';
        $otherUser = $isDirect ? $conversation->users->firstWhere('id', '!=', auth()->id()) : null;
        $chatName = $isDirect ? ($otherUser?->name ?? 'Chat') : ($conversation->title ?? 'Group Chat');
    @endphp
    <div class="flex items-center gap-3">
        <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-sm font-semibold
            {{ $isDirect ? 'bg-[#E26B3D]' : 'bg-[#0f1b3d]' }}">
            @if($isDirect)
                {{ strtoupper(substr($chatName, 0, 1)) }}
            @else
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            @endif
        </div>
        <div>
            <span class="font-semibold text-slate-800">{{ $chatName }}</span>
            @if(!$isDirect)
                <span class="ml-2 text-xs text-slate-400 font-normal">{{ $conversation->users->count() }} members</span>
            @endif
        </div>
    </div>
@endsection

@section('header-actions')
    <a href="{{ route('conversations.index') }}"
       class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-slate-700 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Chats
    </a>

    {{-- Audio & Video call buttons if user has access to calls --}}
    @if(auth()->user()->hasPermission('create_calls'))
        {{-- Audio call button --}}
        <form method="POST" action="{{ route('calls.store') }}" class="inline">
            @csrf
            <input type="hidden" name="conversation_id" value="{{ $conversation->id }}">
            <input type="hidden" name="type" value="audio">
            <button type="submit"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                </svg>
                Audio
            </button>
        </form>

        {{-- Video call button --}}
        <form method="POST" action="{{ route('calls.store') }}" class="inline">
            @csrf
            <input type="hidden" name="conversation_id" value="{{ $conversation->id }}">
            <input type="hidden" name="type" value="video">
            <button type="submit"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium bg-purple-50 text-purple-600 hover:bg-purple-100 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.069A1 1 0 0121 8.87v6.26a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                </svg>
                Video
            </button>
        </form>
    @endif
    @if($conversation->created_by === auth()->id())
        <div x-data="{ confirm: false }">
            <button x-show="!confirm" @click="confirm = true"
                    class="text-sm text-red-400 hover:text-red-600 transition-colors px-2 py-1">
                Delete
            </button>
            <span x-show="confirm" class="flex items-center gap-2">
                <span class="text-sm text-slate-600">Delete chat?</span>
                <form method="POST" action="{{ route('conversations.destroy', $conversation) }}" class="inline">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-sm text-red-600 font-medium hover:underline">Yes</button>
                </form>
                <button @click="confirm = false" class="text-sm text-slate-500 hover:underline">No</button>
            </span>
        </div>
    @endif
@endsection

@php
    $messagesData = $messages->map(fn ($m) => [
        'id'         => $m->id,
        'user_id'    => $m->user_id,
        'body'       => $m->body,
        'type'       => $m->type->value,
        'reply_to'   => $m->reply_to,
        'reply_body' => $m->replyTo?->body,
        'reaction'   => $m->reaction,
        'created_at' => $m->created_at->format('H:i'),
        'sender'     => ['id' => $m->sender?->id, 'name' => $m->sender?->name ?? 'Unknown'],
    ]);
@endphp

@section('content')
<div class="-mx-6 -mt-6 flex flex-col bg-stone-50" style="height: calc(100vh - 4rem)"
     x-data="chatPanel()"
     x-init="$nextTick(() => scrollBottom())"
     x-on:chat-message-received.window="pushMessage($event.detail)">

    {{-- Messages area --}}
    <div x-ref="msgContainer" class="flex-1 overflow-y-auto px-4 py-4 space-y-3">

        @if($messages->isEmpty())
            <div class="flex flex-col items-center justify-center h-full text-slate-400 py-20">
                <svg class="w-12 h-12 mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
                <p class="text-sm">Say hello! 👋</p>
            </div>
        @endif

        {{-- Rendered from Alpine messages array for real-time sync --}}
        <template x-for="msg in messages" :key="msg.id">
            <div :class="msg.user_id === authId ? 'flex justify-end' : 'flex justify-start'"
                 class="group">

                {{-- Other user avatar --}}
                <template x-if="msg.user_id !== authId">
                    <div class="w-8 h-8 rounded-full bg-[#0f1b3d] flex items-center justify-center text-white text-xs font-semibold mr-2 self-end shrink-0"
                         x-text="msg.sender.name ? msg.sender.name[0].toUpperCase() : '?'">
                    </div>
                </template>

                <div class="max-w-xs lg:max-w-md flex flex-col"
                     :class="msg.user_id === authId ? 'items-end' : 'items-start'">

                    {{-- Sender name in group chat --}}
                    <template x-if="msg.user_id !== authId && !isDirect">
                        <span class="text-xs text-slate-400 font-medium mb-0.5 px-1" x-text="msg.sender.name"></span>
                    </template>

                    {{-- Reply context --}}
                    <template x-if="msg.reply_to && msg.reply_body">
                        <div class="text-xs bg-slate-100 border-l-2 border-[#E26B3D] px-2 py-1 rounded mb-1 text-slate-500 max-w-full truncate">
                            <span x-text="msg.reply_body"></span>
                        </div>
                    </template>

                    {{-- Bubble --}}
                    <div class="px-3 py-2 rounded-2xl text-sm leading-relaxed break-words"
                         :class="msg.user_id === authId
                             ? 'bg-[#E26B3D] text-white rounded-br-sm'
                             : 'bg-white text-slate-800 shadow-sm border border-slate-100 rounded-bl-sm'">
                        <span x-text="msg.body"></span>
                    </div>

                    {{-- Reaction + time + actions --}}
                    <div class="flex items-center gap-2 mt-0.5 px-1">
                        <template x-if="msg.reaction">
                            <span class="text-base leading-none cursor-pointer" x-text="msg.reaction"
                                  @click="react(msg.id, msg.reaction)"></span>
                        </template>
                        <span class="text-xs text-slate-400 font-mono" x-text="msg.created_at"></span>

                        {{-- Actions on hover --}}
                        <div class="hidden group-hover:flex items-center gap-1">
                            {{-- Quick reactions --}}
                            <div class="flex gap-0.5">
                                <template x-for="emoji in ['👍','❤️','😂','🔥']">
                                    <button @click="react(msg.id, emoji)"
                                            class="text-sm hover:scale-125 transition-transform leading-none"
                                            x-text="emoji"></button>
                                </template>
                            </div>
                            {{-- Reply --}}
                            <button @click="setReply(msg)"
                                    class="text-slate-400 hover:text-slate-600 transition-colors ml-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>

    {{-- Input area --}}
    @if(isset($oversightMode) && $oversightMode)
    <div class="bg-amber-50 border-t border-amber-200 px-5 py-3 shrink-0 flex items-center gap-2 text-sm text-amber-700">
        <svg class="w-4 h-4 shrink-0 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
        </svg>
        <span><span class="font-semibold">Oversight Mode</span> — Read-only view. You are not a member of this conversation.</span>
    </div>
    @else
    <div class="bg-white border-t border-slate-200 px-4 py-3 shrink-0">

        {{-- Reply preview --}}
        <div x-show="replyTo" x-cloak
             class="flex items-center gap-2 bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 mb-2 text-sm">
            <div class="w-0.5 h-8 bg-[#E26B3D] rounded shrink-0"></div>
            <p class="text-slate-600 truncate flex-1" x-text="replyBody"></p>
            <button @click="clearReply()" class="text-slate-400 hover:text-slate-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div class="flex items-end gap-3">
            <textarea x-ref="input"
                      rows="1"
                      placeholder="Type a message…"
                      class="flex-1 resize-none px-4 py-2.5 border border-slate-200 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-[#E26B3D]/30 focus:border-[#E26B3D] bg-stone-50 max-h-32 overflow-y-auto"
                      @keydown.enter.prevent="!$event.shiftKey && send()"
                      @input="$el.style.height='auto'; $el.style.height=Math.min($el.scrollHeight, 128)+'px'"></textarea>

            <button @click="send()" :disabled="sending"
                    class="w-10 h-10 rounded-full bg-[#E26B3D] hover:bg-[#c95a2f] disabled:opacity-50 text-white flex items-center justify-center shrink-0 transition-colors">
                <template x-if="!sending">
                    <svg class="w-4 h-4 translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                </template>
                <template x-if="sending">
                    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                    </svg>
                </template>
            </button>
        </div>
        <p class="text-xs text-slate-400 mt-1.5 px-1">Enter to send · Shift+Enter for new line</p>
    </div>
    @endif
</div>

<script>
document.addEventListener('alpine:init', function () {
    Alpine.data('chatPanel', function () {
        return {
            replyTo: null,
            replyBody: '',
            sending: false,
            authId: {{ auth()->id() }},
            messages: @json($messagesData),
            isDirect: {{ $isDirect ? 'true' : 'false' }},
            convType: '{{ $conversation->type->value }}',

            setReply(msg) {
                this.replyTo = msg.id;
                this.replyBody = msg.body;
                this.$nextTick(() => this.$refs.input.focus());
            },
            clearReply() { this.replyTo = null; this.replyBody = ''; },

            async send() {
                const body = this.$refs.input.value.trim();
                if (!body || this.sending) return;
                this.sending = true;
                try {
                    const res = await fetch('{{ route('conversations.messages.store', $conversation) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        },
                        body: JSON.stringify({ body, reply_to: this.replyTo }),
                    });
                    const data = await res.json();
                    if (data.success) {
                        this.messages.push(data.message);
                        this.$refs.input.value = '';
                        this.clearReply();
                        this.$nextTick(() => this.scrollBottom());
                    }
                } finally {
                    this.sending = false;
                }
            },

            async react(msgId, emoji) {
                const res = await fetch('{{ route('conversations.messages.react', [$conversation, '__MSG__']) }}'.replace('__MSG__', msgId), {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    },
                    body: JSON.stringify({ reaction: emoji }),
                });
                const data = await res.json();
                if (data.success) {
                    const msg = this.messages.find(m => m.id === msgId);
                    if (msg) msg.reaction = data.reaction;
                }
            },

            scrollBottom() {
                const el = this.$refs.msgContainer;
                if (el) el.scrollTop = el.scrollHeight;
            },

            pushMessage(data) {
                if (!this.messages.find(m => m.id === data.id)) {
                    this.messages.push({
                        id:         data.id,
                        user_id:    data.user_id,
                        body:       data.body,
                        type:       data.type,
                        reply_to:   data.reply_to,
                        reply_body: null,
                        reaction:   data.reaction,
                        created_at: new Date(data.created_at).toLocaleTimeString([], {hour:'2-digit',minute:'2-digit'}),
                        sender:     data.sender,
                    });
                    this.$nextTick(() => this.scrollBottom());
                }
            },
        };
    });
});
</script>

{{-- Pusher real-time --}}
<script src="https://js.pusher.com/8.4.0/pusher.min.js"></script>
<script>
(function () {
    const appKey     = @json(config('broadcasting.connections.pusher.key'));
    const appCluster = @json(config('broadcasting.connections.pusher.options.cluster'));
    const convId     = @json($conversation->id);
    const csrf       = document.querySelector('meta[name="csrf-token"]').content;

    if (!appKey) return; // Pusher not configured yet

    const pusher = new Pusher(appKey, {
        cluster: appCluster,
        authEndpoint: '/broadcasting/auth',
        auth: { headers: { 'X-CSRF-TOKEN': csrf } },
    });

    const channel = pusher.subscribe('private-conversation.' + convId);

    channel.bind('message.sent', function (data) {
        window.dispatchEvent(new CustomEvent('chat-message-received', { detail: data }));
    });

    channel.bind('pusher:subscription_error', function (err) {
        console.warn('Pusher auth failed:', err);
    });
})();
</script>
@endsection

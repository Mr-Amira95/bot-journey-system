@extends('layouts.app')

@php
    $isDirect = $call->conversation->type->value === 'direct';
    $otherUser = $isDirect ? $call->participants->firstWhere('user_id', '!=', auth()->id()) : null;
    $chatName = $isDirect
        ? ($otherUser?->user?->name ?? 'Call')
        : ($call->conversation->title ?? 'Group Call');
    $isVideoCall = $call->type->value === 'video';
@endphp

@section('title', $chatName)

@section('page-title')
    <div class="flex items-center gap-2">
        @if($isVideoCall)
            <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.069A1 1 0 0121 8.87v6.26a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
            </svg>
        @else
            <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
            </svg>
        @endif
        <span>{{ $chatName }}</span>
    </div>
@endsection

@section('header-actions')
    <span class="text-xs text-slate-400 font-mono">App ID: {{ config('services.agora.app_id') }}</span>
    <a href="{{ route('calls.index') }}"
       class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-slate-700 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Call History
    </a>
@endsection

@section('content')
@php
    $participantsData = $call->participants->map(fn ($p) => [
        'user_id'    => $p->user_id,
        'name'       => $p->user?->name ?? 'Unknown',
        'status'     => $p->status->value,
        'is_muted'   => $p->is_muted,
        'is_video_on'=> $p->is_video_on,
    ]);
@endphp

<div class="-mx-6 -mt-6 flex flex-col bg-gray-950 text-white overflow-hidden" style="height: calc(100vh - 4rem)"
     x-data="callPanel()"
     x-init="init()">

    {{-- Agora connection error --}}
    <div x-show="agoraError"
         class="shrink-0 bg-red-900/80 border-b border-red-700 px-4 py-2 text-sm text-red-200 flex items-center gap-2"
         style="display:none;">
        <svg class="w-4 h-4 text-red-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
        <span x-text="'Could not connect to Agora: ' + agoraError"></span>
    </div>

    {{-- Participant declined notification --}}
    <div x-show="rejectedBy"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-2"
         class="shrink-0 bg-yellow-900/80 border-b border-yellow-700 px-4 py-2 text-sm text-yellow-200 flex items-center justify-between gap-2"
         style="display:none;">
        <div class="flex items-center gap-2">
            <svg class="w-4 h-4 text-yellow-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span x-text="rejectedBy + ' declined the call'"></span>
        </div>
        <button @click="rejectedBy = null" class="text-yellow-400 hover:text-yellow-200 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    {{-- ── Video Grid ─────────────────────────────────────────── --}}
    <div class="flex-1 overflow-hidden p-3">
        <div class="h-full grid gap-3 auto-rows-fr" :class="videoGridClass">

            {{-- Local video / audio tile --}}
            <div class="relative rounded-2xl overflow-hidden bg-gray-800 flex items-center justify-center group">
                @if($isVideoCall)
                    <div id="local-video" class="absolute inset-0 w-full h-full"></div>
                @endif

                {{-- Avatar fallback (audio call or video off) --}}
                <div class="flex flex-col items-center gap-2 z-10 pointer-events-none"
                     :class="isVideoCall && !isVideoOff ? 'hidden' : 'flex'">
                    <div class="w-20 h-20 rounded-full bg-[#E26B3D] flex items-center justify-center text-3xl font-bold text-white select-none">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <span class="text-sm text-white/80 font-medium">You</span>
                </div>

                {{-- Name tag --}}
                <div class="absolute bottom-3 left-3 flex items-center gap-1.5 bg-black/60 backdrop-blur-sm px-2 py-1 rounded-lg">
                    <span class="text-xs text-white font-medium">{{ auth()->user()->name }}</span>
                    <span x-show="isMuted" class="text-red-400">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24"><path d="M19 11h-1.7c0 .74-.16 1.43-.43 2.05l1.23 1.23c.56-.98.9-2.09.9-3.28zm-4.02.17c0-.06.02-.11.02-.17V5c0-1.66-1.34-3-3-3S9 3.34 9 5v.18l5.98 5.99zM4.27 3L3 4.27l6.01 6.01V11c0 1.66 1.33 3 2.99 3 .22 0 .44-.03.65-.08l1.66 1.66c-.71.33-1.5.52-2.31.52-2.76 0-5.3-2.1-5.3-5.1H5c0 3.41 2.72 6.23 6 6.72V21h2v-3.28c.91-.13 1.77-.45 2.54-.9L19.73 21 21 19.73 4.27 3z"/></svg>
                    </span>
                </div>
            </div>

            {{-- Remote participants --}}
            <template x-for="rUser in remoteUsers" :key="rUser.uid">
                <div class="relative rounded-2xl overflow-hidden bg-gray-800 flex items-center justify-center group">
                    <div :id="'remote-' + rUser.uid" class="absolute inset-0 w-full h-full"></div>

                    {{-- Avatar fallback --}}
                    <div class="flex flex-col items-center gap-2 z-10 pointer-events-none"
                         :class="rUser.hasVideo ? 'hidden' : 'flex'">
                        <div class="w-20 h-20 rounded-full bg-[#0f1b3d] flex items-center justify-center text-3xl font-bold text-white select-none"
                             x-text="participantName(rUser.uid)[0].toUpperCase()"></div>
                        <span class="text-sm text-white/80 font-medium" x-text="participantName(rUser.uid)"></span>
                    </div>

                    {{-- Name tag --}}
                    <div class="absolute bottom-3 left-3 flex items-center gap-1.5 bg-black/60 backdrop-blur-sm px-2 py-1 rounded-lg">
                        <span class="text-xs text-white font-medium" x-text="participantName(rUser.uid)"></span>
                    </div>
                </div>
            </template>

            {{-- Waiting placeholder when no remote users yet --}}
            <template x-if="remoteUsers.length === 0">
                <div class="rounded-2xl bg-gray-800/50 border-2 border-dashed border-gray-700 flex flex-col items-center justify-center gap-3 text-gray-500">
                    <svg class="w-10 h-10 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <p class="text-sm">Waiting for others to join…</p>
                </div>
            </template>
        </div>
    </div>

    {{-- ── Controls Bar ────────────────────────────────────────── --}}
    <div class="shrink-0 bg-gray-900 border-t border-gray-800 px-6 py-4">
        <div class="flex items-center justify-between max-w-2xl mx-auto">

            {{-- Timer --}}
            <div class="text-sm font-mono text-gray-400 w-24">
                <span x-text="formattedDuration"></span>
            </div>

            {{-- Main controls --}}
            <div class="flex items-center gap-3">

                {{-- Mute button --}}
                <button @click="toggleMic()"
                        :class="isMuted ? 'bg-red-600 hover:bg-red-700 text-white' : 'bg-gray-700 hover:bg-gray-600 text-white'"
                        class="w-12 h-12 rounded-full flex items-center justify-center transition-colors"
                        :title="isMuted ? 'Unmute' : 'Mute'">
                    <template x-if="!isMuted">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 14c1.66 0 3-1.34 3-3V5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3zm5.91-3c-.49 0-.9.36-.98.85C16.52 14.2 14.47 16 12 16s-4.52-1.8-4.93-4.15c-.08-.49-.49-.85-.98-.85-.61 0-1.09.54-1 1.14.49 3 2.89 5.35 5.91 5.78V20c0 .55.45 1 1 1s1-.45 1-1v-2.08c3.02-.43 5.42-2.78 5.91-5.78.1-.6-.39-1.14-1-1.14z"/></svg>
                    </template>
                    <template x-if="isMuted">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M19 11h-1.7c0 .74-.16 1.43-.43 2.05l1.23 1.23c.56-.98.9-2.09.9-3.28zm-4.02.17c0-.06.02-.11.02-.17V5c0-1.66-1.34-3-3-3S9 3.34 9 5v.18l5.98 5.99zM4.27 3L3 4.27l6.01 6.01V11c0 1.66 1.33 3 2.99 3 .22 0 .44-.03.65-.08l1.66 1.66c-.71.33-1.5.52-2.31.52-2.76 0-5.3-2.1-5.3-5.1H5c0 3.41 2.72 6.23 6 6.72V21h2v-3.28c.91-.13 1.77-.45 2.54-.9L19.73 21 21 19.73 4.27 3z"/></svg>
                    </template>
                </button>

                {{-- Camera button (video calls only) --}}
                @if($isVideoCall)
                    <button @click="toggleCamera()"
                            :class="isVideoOff ? 'bg-red-600 hover:bg-red-700 text-white' : 'bg-gray-700 hover:bg-gray-600 text-white'"
                            class="w-12 h-12 rounded-full flex items-center justify-center transition-colors"
                            :title="isVideoOff ? 'Turn on camera' : 'Turn off camera'">
                        <template x-if="!isVideoOff">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17 10.5V7c0-.55-.45-1-1-1H4c-.55 0-1 .45-1 1v10c0 .55.45 1 1 1h12c.55 0 1-.45 1-1v-3.5l4 4v-11l-4 4z"/></svg>
                        </template>
                        <template x-if="isVideoOff">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M21 6.5l-4 4V7c0-.55-.45-1-1-1H9.82L21 17.18V6.5zM3.27 2L2 3.27 4.73 6H4c-.55 0-1 .45-1 1v10c0 .55.45 1 1 1h12c.21 0 .39-.08.54-.18L19.73 21 21 19.73 3.27 2z"/></svg>
                        </template>
                    </button>
                @endif

                {{-- Screen share button --}}
                <button @click="toggleScreenShare()"
                        :class="isScreenSharing ? 'bg-[#E26B3D] hover:bg-[#c95a2f] text-white' : 'bg-gray-700 hover:bg-gray-600 text-white'"
                        class="w-12 h-12 rounded-full flex items-center justify-center transition-colors"
                        title="Screen share">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M20 18c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2H4c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2H0v2h24v-2h-4zM4 6h16v10H4V6z"/></svg>
                </button>

                {{-- Leave button --}}
                @if($call->started_by !== auth()->id())
                    <button @click="leaveCall()" :disabled="isEnding"
                            class="w-12 h-12 rounded-full bg-yellow-600 hover:bg-yellow-700 disabled:opacity-50 text-white flex items-center justify-center transition-colors"
                            title="Leave call">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/></svg>
                    </button>
                @endif

                {{-- End call button (only starter) --}}
                @if($call->started_by === auth()->id())
                    <button @click="endCall()" :disabled="isEnding"
                            class="px-5 h-12 rounded-full bg-red-600 hover:bg-red-700 disabled:opacity-50 text-white font-semibold text-sm flex items-center gap-2 transition-colors"
                            title="End call for everyone">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/></svg>
                        End Call
                    </button>
                @endif
            </div>

            {{-- Participants summary --}}
            <div class="flex items-center gap-1 w-24 justify-end">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                <span class="text-sm text-gray-400" x-text="remoteUsers.length + 1"></span>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', function () {
    Alpine.data('callPanel', function () {
        return {
            callId: {{ $call->id }},
            channelName: @json($channelName),
            agoraAppId: @json($agoraAppId),
            agoraToken: @json($agoraToken),
            uid: {{ auth()->id() }},
            isVideoCall: {{ $isVideoCall ? 'true' : 'false' }},
            isMuted: false,
            isVideoOff: false,
            isScreenSharing: false,
            isEnding: false,
            agoraError: null,
            rejectedBy: null,
            _rejectedTimeout: null,
            status: @json($call->status->value),
            participants: @json($participantsData),
            remoteUsers: [],
            duration: 0,
            _durationInterval: null,
            _client: null,
            _micTrack: null,
            _cameraTrack: null,
            _screenTrack: null,

            get formattedDuration() {
                const h = Math.floor(this.duration / 3600);
                const m = Math.floor((this.duration % 3600) / 60);
                const s = this.duration % 60;
                if (h > 0) return h + ':' + String(m).padStart(2,'0') + ':' + String(s).padStart(2,'0');
                return String(m).padStart(2,'0') + ':' + String(s).padStart(2,'0');
            },

            get videoGridClass() {
                const total = this.remoteUsers.length + 1;
                if (total === 1) return 'grid-cols-1';
                if (total === 2) return 'grid-cols-2';
                if (total <= 4) return 'grid-cols-2';
                return 'grid-cols-3';
            },

            async init() {
                try {
                    await this.joinAgora();
                } catch (err) {
                    console.error('[Agora] join failed:', err.code, err.message, err);
                    this.agoraError = (err.code ? '[' + err.code + '] ' : '') + (err.message || 'Unknown error');
                }
                this.startTimer();
                this.subscribePusher();
            },

            async joinAgora() {
                console.log('[Agora] channel:', this.channelName, '| uid:', this.uid, '| appId:', this.agoraAppId);
                if (typeof AgoraRTC === 'undefined') {
                    throw new Error('Agora SDK not loaded');
                }
                this._client = AgoraRTC.createClient({ mode: 'rtc', codec: 'vp8' });

                this._client.on('user-published', async (user, mediaType) => {
                    await this._client.subscribe(user, mediaType);
                    if (mediaType === 'video') {
                        const existing = this.remoteUsers.find(u => u.uid === user.uid);
                        if (!existing) {
                            this.remoteUsers = [...this.remoteUsers, { uid: user.uid, hasVideo: true, hasAudio: false }];
                        } else {
                            existing.hasVideo = true;
                        }
                        setTimeout(() => {
                            const el = document.getElementById('remote-' + user.uid);
                            if (el) user.videoTrack.play(el);
                        }, 100);
                    }
                    if (mediaType === 'audio') {
                        const existing = this.remoteUsers.find(u => u.uid === user.uid);
                        if (!existing) {
                            this.remoteUsers = [...this.remoteUsers, { uid: user.uid, hasVideo: false, hasAudio: true }];
                        } else {
                            existing.hasAudio = true;
                        }
                        user.audioTrack.play();
                    }
                });

                this._client.on('user-unpublished', (user, mediaType) => {
                    if (mediaType === 'audio') user.audioTrack?.stop();
                    if (mediaType === 'video') {
                        user.videoTrack?.stop();
                        const u = this.remoteUsers.find(u => u.uid === user.uid);
                        if (u) u.hasVideo = false;
                    }
                });

                this._client.on('user-left', (user) => {
                    this.remoteUsers = this.remoteUsers.filter(u => u.uid !== user.uid);
                });

                await this._client.join(this.agoraAppId, this.channelName, this.agoraToken, this.uid);

                if (this.isVideoCall) {
                    [this._micTrack, this._cameraTrack] = await AgoraRTC.createMicrophoneAndCameraTracks(
                        {}, { encoderConfig: { width: 640, height: 480, frameRate: 24, bitrateMax: 800 } }
                    );
                    const localEl = document.getElementById('local-video');
                    if (localEl) this._cameraTrack.play(localEl);
                    await this._client.publish([this._micTrack, this._cameraTrack]);
                } else {
                    this._micTrack = await AgoraRTC.createMicrophoneAudioTrack();
                    await this._client.publish([this._micTrack]);
                }
            },

            async toggleMic() {
                if (!this._micTrack) return;
                this.isMuted = !this.isMuted;
                await this._micTrack.setEnabled(!this.isMuted);
                this.updateParticipantStatus({ is_muted: this.isMuted });
            },

            async toggleCamera() {
                if (!this._cameraTrack) return;
                this.isVideoOff = !this.isVideoOff;
                await this._cameraTrack.setEnabled(!this.isVideoOff);
                this.updateParticipantStatus({ is_video_on: !this.isVideoOff });
            },

            async toggleScreenShare() {
                if (this.isScreenSharing) {
                    this._screenTrack?.stop();
                    this._screenTrack?.close();
                    if (this._cameraTrack && !this.isVideoOff) {
                        await this._client.unpublish(this._screenTrack);
                        await this._client.publish(this._cameraTrack);
                        const localEl = document.getElementById('local-video');
                        if (localEl) this._cameraTrack.play(localEl);
                    }
                    this.isScreenSharing = false;
                    this.updateParticipantStatus({ is_screen_sharing: false });
                } else {
                    try {
                        this._screenTrack = await AgoraRTC.createScreenVideoTrack({}, 'disable');
                        if (this._cameraTrack) {
                            await this._client.unpublish(this._cameraTrack);
                            this._cameraTrack.stop();
                        }
                        await this._client.publish(this._screenTrack);
                        const localEl = document.getElementById('local-video');
                        if (localEl) this._screenTrack.play(localEl);
                        this.isScreenSharing = true;
                        this.updateParticipantStatus({ is_screen_sharing: true });
                        this._screenTrack.on('track-ended', () => { this.toggleScreenShare(); });
                    } catch (err) {
                        console.warn('Screen share cancelled or failed:', err.message);
                    }
                }
            },

            async endCall() {
                if (this.isEnding) return;
                this.isEnding = true;
                this._micTrack?.stop(); this._micTrack?.close();
                this._cameraTrack?.stop(); this._cameraTrack?.close();
                this._screenTrack?.stop(); this._screenTrack?.close();
                await this._client?.leave();
                try {
                    await fetch('/calls/{{ $call->id }}/end', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                    });
                } catch (_) {}
                window.location.href = '/calls';
            },

            async leaveCall() {
                if (this.isEnding) return;
                this.isEnding = true;
                this._micTrack?.stop(); this._micTrack?.close();
                this._cameraTrack?.stop(); this._cameraTrack?.close();
                this._screenTrack?.stop(); this._screenTrack?.close();
                await this._client?.leave();
                try {
                    await fetch('/calls/{{ $call->id }}/leave', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                    });
                } catch (_) {}
                window.location.href = '/calls';
            },

            async updateParticipantStatus(data) {
                fetch('/calls/{{ $call->id }}/participant', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                    body: JSON.stringify(data),
                }).catch(() => {});
            },

            startTimer() {
                this._durationInterval = setInterval(() => this.duration++, 1000);
            },

            subscribePusher() {
                const pusherKey = @json(config('broadcasting.connections.pusher.key'));
                if (!pusherKey || typeof Pusher === 'undefined') return;
                const pusher = new Pusher(pusherKey, {
                    cluster: @json(config('broadcasting.connections.pusher.options.cluster')),
                    authEndpoint: '/broadcasting/auth',
                    auth: { headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content } },
                });
                const ch = pusher.subscribe('private-call.' + this.callId);
                ch.bind('call.status', (data) => {
                    if ((data.event === 'call_ended' || data.event === 'call_rejected') && !this.isEnding) {
                        this.isEnding = true;
                        clearInterval(this._durationInterval);
                        this._micTrack?.stop(); this._micTrack?.close();
                        this._cameraTrack?.stop(); this._cameraTrack?.close();
                        this._screenTrack?.stop(); this._screenTrack?.close();
                        this._client?.leave().finally(() => { window.location.href = '/calls'; });
                    }
                    if (data.event === 'participant_rejected') {
                        const p = this.participants.find(p => p.user_id === data.user_id);
                        if (p) {
                            this.rejectedBy = p.name;
                            clearTimeout(this._rejectedTimeout);
                            this._rejectedTimeout = setTimeout(() => { this.rejectedBy = null; }, 5000);
                        }
                    }
                    if (data.event === 'participant_updated') {
                        const p = this.participants.find(p => p.user_id === data.user_id);
                        if (p) {
                            if (data.is_muted !== undefined) p.is_muted = data.is_muted;
                            if (data.is_video_on !== undefined) p.is_video_on = data.is_video_on;
                            if (data.is_screen_sharing !== undefined) p.is_screen_sharing = data.is_screen_sharing;
                        }
                    }
                });
            },

            participantName(uid) {
                const p = this.participants.find(p => p.user_id === uid);
                return p ? p.name : 'User ' + uid;
            },
        };
    });
});
</script>

{{-- Agora SDK --}}
<script src="https://download.agora.io/sdk/release/AgoraRTC_N-4.22.1.js"></script>
@endsection

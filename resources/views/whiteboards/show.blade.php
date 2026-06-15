@extends('layouts.app')

@section('title', $whiteboard->title)
@section('page-title')
<span id="boardTitleDisplay"
      @if($isOwner) class="cursor-pointer hover:text-[#E26B3D] transition-colors" title="Click to rename" @endif>{{ $whiteboard->title }}</span>@if($isOwner)<input id="boardTitleInput"
       class="hidden border-b border-[#E26B3D] outline-none bg-transparent text-lg font-semibold text-slate-800"
       value="{{ $whiteboard->title }}"
       style="min-width:200px;max-width:420px">@endif
@endsection

@section('header-actions')
    <a href="{{ route('whiteboards.index') }}"
       class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm text-slate-600 border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Boards
    </a>
    <button id="downloadBtn"
            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm text-slate-600 border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
        </svg>
        Download
    </button>
    @if($canEdit)
        <button id="saveBtn"
                class="inline-flex items-center gap-1.5 px-4 py-1.5 text-sm text-white bg-[#E26B3D] rounded-lg hover:bg-[#c85a2f] transition-colors font-medium">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
            </svg>
            Save
        </button>
    @endif
    @if($isOwner)
        <button onclick="document.getElementById('shareModal').classList.remove('hidden')"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm text-slate-600 border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            Share
        </button>
    @endif
@endsection

@section('content')
<div class="-mx-6 -mt-6 flex flex-col overflow-hidden" id="whiteboardApp" style="height: calc(100vh - 64px)">

    @if($canEdit)
    {{-- ── Editing toolbar ── --}}
    <div id="toolbar" class="bg-white border-b border-slate-200 px-3 py-2 flex items-center gap-2 shrink-0 overflow-x-auto">

        {{-- Drawing tools --}}
        <div class="flex items-center gap-0.5 bg-slate-100 rounded-lg p-0.5 shrink-0">
            <button data-tool="pen" title="Pen (P)"
                    class="tool-btn active-tool flex items-center gap-1.5 px-2.5 py-1.5 rounded-md text-xs font-medium transition-colors text-white bg-[#E26B3D]">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                </svg>
                <span>Pen</span>
            </button>
            <button data-tool="pencil" title="Pencil (C)"
                    class="tool-btn flex items-center gap-1.5 px-2.5 py-1.5 rounded-md text-xs font-medium transition-colors text-slate-600 hover:bg-white/60">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                <span>Pencil</span>
            </button>
            <button data-tool="marker" title="Marker (M)"
                    class="tool-btn flex items-center gap-1.5 px-2.5 py-1.5 rounded-md text-xs font-medium transition-colors text-slate-600 hover:bg-white/60">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                </svg>
                <span>Marker</span>
            </button>
            <button data-tool="eraser" title="Eraser (E)"
                    class="tool-btn flex items-center gap-1.5 px-2.5 py-1.5 rounded-md text-xs font-medium transition-colors text-slate-600 hover:bg-white/60">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L17.94 6.06a1.5 1.5 0 012.12 2.12L8.12 20.24A4 4 0 015.29 21H4v-1.29A4 4 0 016 18z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21h18"/>
                </svg>
                <span>Eraser</span>
            </button>
        </div>

        <div class="w-px h-8 bg-slate-200 shrink-0"></div>

        {{-- Overlay tools --}}
        <div class="flex items-center gap-0.5 bg-slate-100 rounded-lg p-0.5 shrink-0">
            <button data-tool="sticky" title="Sticky Note (N)"
                    class="tool-btn flex items-center gap-1.5 px-2.5 py-1.5 rounded-md text-xs font-medium transition-colors text-slate-600 hover:bg-white/60">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                </svg>
                <span>Sticky</span>
            </button>
            <button data-tool="text" title="Text (T)"
                    class="tool-btn flex items-center gap-1.5 px-2.5 py-1.5 rounded-md text-xs font-medium transition-colors text-slate-600 hover:bg-white/60">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h8m-8 6h16"/>
                </svg>
                <span>Text</span>
            </button>
        </div>

        <div class="w-px h-8 bg-slate-200 shrink-0"></div>

        {{-- Color palette --}}
        <div class="flex items-center gap-1.5 shrink-0">
            <button id="activeColorBtn" title="Custom color"
                    class="w-7 h-7 rounded-full border-2 border-slate-300 shrink-0 hover:scale-110 transition-transform shadow-sm"
                    style="background:#000000"
                    onclick="document.getElementById('colorPicker').click()">
            </button>
            <input type="color" id="colorPicker" class="sr-only" value="#000000">
            {{-- Expand/collapse toggle --}}
            <button id="colorExpandBtn" title="Toggle colors"
                    class="w-5 h-5 flex items-center justify-center rounded text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors shrink-0">
                <svg id="colorChevron" class="w-3.5 h-3.5 transition-transform duration-150" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </button>
            {{-- Swatches — hidden by default --}}
            <div id="colorSwatches" class="hidden items-center gap-1 flex-wrap">
                @foreach([
                    '#000000' => 'Black',
                    '#ffffff' => 'White',
                    '#ef4444' => 'Red',
                    '#f97316' => 'Orange',
                    '#eab308' => 'Yellow',
                    '#22c55e' => 'Green',
                    '#06b6d4' => 'Cyan',
                    '#3b82f6' => 'Blue',
                    '#8b5cf6' => 'Purple',
                    '#ec4899' => 'Pink',
                    '#6b7280' => 'Gray',
                ] as $hex => $name)
                    <button data-color="{{ $hex }}" title="{{ $name }}"
                            class="color-swatch w-5 h-5 rounded-full shrink-0 border border-slate-200 hover:scale-125 transition-transform"
                            style="background:{{ $hex }}"></button>
                @endforeach
            </div>
        </div>

        <div class="w-px h-8 bg-slate-200 shrink-0"></div>

        {{-- Brush size (drawing tools – shown by default) --}}
        <div id="sizeControls" class="flex items-center gap-2 shrink-0">
            <span class="text-xs text-slate-400 shrink-0">Size</span>
            <svg class="w-2.5 h-2.5 text-slate-400 shrink-0" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="1.5"/></svg>
            <input type="range" id="brushSize" min="1" max="60" value="4"
                   class="w-24 accent-[#E26B3D] cursor-pointer">
            <svg class="w-4 h-4 text-slate-400 shrink-0" fill="currentColor" viewBox="0 0 16 16"><circle cx="8" cy="8" r="5"/></svg>
            <span id="sizeLabel" class="text-xs text-slate-600 font-mono w-8 shrink-0 text-right">4px</span>
        </div>

        {{-- Font size (text tool – hidden by default) --}}
        <div id="fontSizeControls" class="hidden items-center gap-2 shrink-0">
            <span class="text-xs text-slate-400 shrink-0">Size</span>
            <select id="fontSizeSelect"
                    class="text-xs border border-slate-200 rounded px-1.5 py-1 text-slate-700 focus:outline-none focus:ring-1 focus:ring-[#E26B3D]/40 cursor-pointer">
                <option value="12">12</option>
                <option value="14">14</option>
                <option value="16">16</option>
                <option value="18" selected>18</option>
                <option value="20">20</option>
                <option value="24">24</option>
                <option value="28">28</option>
                <option value="32">32</option>
                <option value="40">40</option>
                <option value="48">48</option>
                <option value="60">60</option>
                <option value="72">72</option>
            </select>
            <span class="text-xs text-slate-400">px</span>
        </div>

        <div class="w-px h-8 bg-slate-200 shrink-0"></div>

        {{-- Attach --}}
        <button id="attachBtn" title="Attach image, file, or video"
                class="flex items-center gap-1 px-2 py-1.5 rounded-lg text-slate-500 hover:bg-slate-100 hover:text-slate-700 transition-colors shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
            </svg>
            <span class="text-xs">Attach</span>
        </button>

        <div class="flex-1 min-w-[0.5rem] shrink-0"></div>

        {{-- Zoom controls --}}
        <div class="flex items-center gap-0.5 shrink-0">
            <button id="zoomOutBtn" title="Zoom Out (Ctrl + −)"
                    class="flex items-center justify-center w-7 h-7 rounded-lg text-slate-500 hover:bg-slate-100 hover:text-slate-700 transition-colors text-base leading-none font-semibold">
                −
            </button>
            <button id="zoomLabel" title="Reset to 100% (Ctrl+0)"
                    class="px-2 py-0.5 rounded text-xs font-mono text-slate-600 hover:bg-slate-100 transition-colors min-w-[3.5rem] text-center">
                100%
            </button>
            <button id="zoomInBtn" title="Zoom In (Ctrl + =)"
                    class="flex items-center justify-center w-7 h-7 rounded-lg text-slate-500 hover:bg-slate-100 hover:text-slate-700 transition-colors text-base leading-none font-semibold">
                +
            </button>
        </div>

        <div class="w-px h-8 bg-slate-200 shrink-0"></div>

        {{-- Undo --}}
        <button id="undoBtn" title="Undo (Ctrl+Z)"
                class="flex items-center gap-1 px-2 py-1.5 rounded-lg text-slate-500 hover:bg-slate-100 hover:text-slate-700 transition-colors shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
            </svg>
            <span class="text-xs">Undo</span>
        </button>

        <div class="w-px h-8 bg-slate-200 shrink-0"></div>

        {{-- Clear --}}
        <button onclick="document.getElementById('clearModal').classList.remove('hidden')"
                title="Clear board"
                class="flex items-center gap-1 px-2 py-1.5 rounded-lg text-slate-500 hover:bg-red-50 hover:text-red-500 transition-colors shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
            <span class="text-xs">Clear</span>
        </button>

    </div>
    @else
    {{-- View-only bar --}}
    <div class="bg-amber-50 border-b border-amber-200 px-4 py-2 flex items-center gap-3 shrink-0">
        <svg class="w-4 h-4 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
        </svg>
        <span class="text-sm font-medium text-amber-700">View only</span>
        <span class="text-xs text-amber-500">by {{ $whiteboard->user->name }}</span>
    </div>
    @endif

    {{-- Board viewport: always fills remaining space, canvas always covers it entirely --}}
    <div id="boardViewport" class="flex-1 overflow-hidden" style="position:relative; background:white">
        {{-- Canvas wrap: CSS transform-origin 0 0 for zoom — canvas is sized so visual = viewport --}}
        <div id="canvasWrap" style="position:absolute; top:0; left:0; transform-origin:0 0">
            <canvas id="wb" style="display:block; background:white; cursor:{{ $canEdit ? 'crosshair' : 'default' }}"></canvas>
            {{-- Overlay layer for sticky notes, text boxes, images, videos, file cards --}}
            <div id="elementsLayer" style="position:absolute; top:0; left:0; width:100%; height:100%; pointer-events:none; overflow:visible"></div>
        </div>
    </div>

</div>

{{-- ── Clear confirmation modal ── --}}
@if($canEdit)
<div id="clearModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-xl p-6 shadow-2xl max-w-sm w-full mx-4">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <div>
                <h3 class="text-base font-semibold text-slate-800">Clear board?</h3>
                <p class="text-sm text-slate-500">Everything will be erased. This cannot be undone.</p>
            </div>
        </div>
        <div class="flex justify-end gap-3 mt-5">
            <button onclick="document.getElementById('clearModal').classList.add('hidden')"
                    class="px-4 py-2 text-sm text-slate-600 border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors">
                Cancel
            </button>
            <button id="clearConfirmBtn"
                    class="px-4 py-2 text-sm text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors">
                Clear All
            </button>
        </div>
    </div>
</div>
@endif

@if($isOwner)
{{-- ── Share modal ── --}}
<div id="shareModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full mx-4 max-h-[80vh] flex flex-col">
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-200 shrink-0">
            <h3 class="text-base font-semibold text-slate-800">Share "{{ $whiteboard->title }}"</h3>
            <button onclick="document.getElementById('shareModal').classList.add('hidden')"
                    class="p-1.5 rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div class="p-5 overflow-y-auto flex-1">

            @if($whiteboard->shares->count() > 0)
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Shared with</p>
                <div class="space-y-2 mb-5">
                    @foreach($whiteboard->shares as $share)
                        <div class="flex items-center justify-between py-1">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-full bg-[#E26B3D] flex items-center justify-center text-white text-sm font-semibold shrink-0">
                                    {{ strtoupper(substr($share->sharedWith->name, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-slate-800">{{ $share->sharedWith->name }}</p>
                                    <p class="text-xs text-slate-400 font-mono">{{ $share->sharedWith->email }}</p>
                                </div>
                            </div>
                            <form action="{{ route('whiteboards.unshare', [$whiteboard, $share]) }}" method="POST">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-xs text-red-500 hover:text-red-700 hover:underline transition-colors">
                                    Remove
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @endif

            @php
                $alreadySharedIds = $whiteboard->shares->pluck('shared_with_user_id')->toArray();
                $available = $users->filter(fn($u) => !in_array($u->id, $alreadySharedIds));
            @endphp

            @if($available->count() > 0)
                <form action="{{ route('whiteboards.share', $whiteboard) }}" method="POST">
                    @csrf
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Add people</p>
                    <div class="relative mb-2">
                        <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
                        </svg>
                        <input id="shareSearch" type="text" placeholder="Search by name or email…" autocomplete="off"
                               class="w-full pl-8 pr-3 py-1.5 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#E26B3D]/30 focus:border-[#E26B3D]">
                    </div>
                    <div class="space-y-1 max-h-52 overflow-y-auto mb-4 border border-slate-200 rounded-lg divide-y divide-slate-100" id="shareUserList">
                        @foreach($available as $user)
                            <label class="share-user-row flex items-center gap-3 px-3 py-2.5 hover:bg-slate-50 cursor-pointer"
                                   data-name="{{ strtolower($user->name) }}"
                                   data-email="{{ strtolower($user->email) }}">
                                <input type="checkbox" name="user_ids[]" value="{{ $user->id }}"
                                       class="rounded text-[#E26B3D] accent-[#E26B3D]">
                                <div class="w-7 h-7 rounded-full bg-slate-200 flex items-center justify-center text-slate-600 text-xs font-semibold shrink-0">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="text-sm text-slate-800">{{ $user->name }}</p>
                                    <p class="text-xs text-slate-400 font-mono">{{ $user->email }}</p>
                                </div>
                            </label>
                        @endforeach
                        <p id="shareNoResults" class="hidden px-3 py-4 text-sm text-slate-400 text-center">No users found.</p>
                    </div>
                    <button type="submit"
                            class="w-full py-2 text-sm font-medium bg-[#E26B3D] text-white rounded-lg hover:bg-[#c85a2f] transition-colors">
                        Share Access
                    </button>
                </form>
            @else
                <p class="text-sm text-slate-400 text-center py-4">All users already have access.</p>
            @endif
        </div>
    </div>
</div>
@endif

<script>
(function () {
    'use strict';

    const canvas    = document.getElementById('wb');
    const ctx       = canvas.getContext('2d');
    const wrap      = document.getElementById('canvasWrap');
    const viewport  = document.getElementById('boardViewport');
    const elemLayer = document.getElementById('elementsLayer');
    const isOwner   = @json($canEdit);

    // ── Board dimensions (large fixed canvas for panning) ────────────────────
    const BOARD_W = 4000;
    const BOARD_H = 3000;

    // ── State ─────────────────────────────────────────────────────────────────
    let tool         = 'pen';
    let color        = '#000000';
    let size         = 4;
    let textFontSize = 18;
    let zoom         = 1.0;
    let panX         = 0;   // canvas logical pixels from left edge
    let panY         = 0;   // canvas logical pixels from top edge
    let isDrawing    = false;
    let lastX = 0, lastY = 0;

    // ── Elements ──────────────────────────────────────────────────────────────
    let elements   = [];
    let nextElemId = 1;

    // ── Undo stack (data URLs — ImageData would be ~48 MB each on the large board) ──
    const undoStack = [];
    const MAX_UNDO  = 30;

    function saveState() {
        if (undoStack.length >= MAX_UNDO) undoStack.shift();
        undoStack.push(canvas.toDataURL('image/png'));
    }

    function undo() {
        if (undoStack.length <= 1) return;
        undoStack.pop();
        const dataURL = undoStack[undoStack.length - 1];
        const img     = new Image();
        img.onload = () => {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            ctx.fillStyle = '#ffffff';
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
        };
        img.src = dataURL;
    }

    // ── Canvas init ───────────────────────────────────────────────────────────
    function initCanvas() {
        canvas.width  = BOARD_W;
        canvas.height = BOARD_H;
        wrap.style.width  = BOARD_W + 'px';
        wrap.style.height = BOARD_H + 'px';

        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, BOARD_W, BOARD_H);

        const url = @json($whiteboard->thumbnail_url ?? null);
        if (url) {
            const img = new Image();
            img.crossOrigin = 'anonymous';
            img.onload = () => {
                // New saves are BOARD_W × BOARD_H; old saves are viewport-sized → draw at top-left as-is
                if (img.naturalWidth === BOARD_W && img.naturalHeight === BOARD_H) {
                    ctx.drawImage(img, 0, 0, BOARD_W, BOARD_H);
                } else {
                    ctx.drawImage(img, 0, 0, img.naturalWidth, img.naturalHeight);
                }
                saveState();
            };
            img.onerror = () => saveState();
            img.src = url + '?nc=' + Date.now();
        } else {
            saveState();
        }

        applyTransform();
    }

    // ── Pan + Zoom ────────────────────────────────────────────────────────────
    // transform: scale(zoom) translate(-panX px, -panY px)
    // → canvas pixel (panX, panY) appears at viewport (0, 0)
    // → visual always covers the full viewport regardless of zoom level
    function applyTransform() {
        wrap.style.transform = 'scale(' + zoom + ') translate(' + (-panX) + 'px, ' + (-panY) + 'px)';
    }

    function clampPan() {
        const vpW = viewport.clientWidth;
        const vpH = viewport.clientHeight;
        panX = Math.max(0, Math.min(panX, BOARD_W - vpW  / zoom));
        panY = Math.max(0, Math.min(panY, BOARD_H - vpH / zoom));
    }

    // Zoom toward a pivot point (screen coords). Defaults to viewport center.
    function setZoom(z, pivotScreenX, pivotScreenY) {
        const vpW = viewport.clientWidth;
        const vpH = viewport.clientHeight;
        const px  = pivotScreenX !== undefined ? pivotScreenX : vpW / 2;
        const py  = pivotScreenY !== undefined ? pivotScreenY : vpH / 2;

        // Canvas coordinate under the pivot (stays fixed after zoom)
        const cx = px / zoom + panX;
        const cy = py / zoom + panY;

        zoom = Math.max(0.25, Math.min(4.0, Math.round(z * 10) / 10));

        panX = cx - px / zoom;
        panY = cy - py / zoom;
        clampPan();
        applyTransform();

        document.getElementById('zoomLabel').textContent = Math.round(zoom * 100) + '%';
    }

    document.getElementById('zoomInBtn')?.addEventListener('click',  () => setZoom(zoom + 0.1));
    document.getElementById('zoomOutBtn')?.addEventListener('click', () => setZoom(zoom - 0.1));
    document.getElementById('zoomLabel')?.addEventListener('click',  () => {
        zoom = 1.0; panX = 0; panY = 0; applyTransform();
        document.getElementById('zoomLabel').textContent = '100%';
    });

    // Wheel: pan on plain scroll / two-finger swipe; zoom on Ctrl+scroll
    viewport.addEventListener('wheel', (e) => {
        e.preventDefault();
        if (e.ctrlKey || e.metaKey) {
            const rect = viewport.getBoundingClientRect();
            setZoom(zoom + (e.deltaY > 0 ? -0.1 : 0.1),
                    e.clientX - rect.left,
                    e.clientY - rect.top);
        } else {
            const factor = e.deltaMode === 1 ? 20 : 1; // LINE → px
            panX += e.deltaX * factor / zoom;
            panY += e.deltaY * factor / zoom;
            clampPan();
            applyTransform();
        }
    }, { passive: false });

    // ── Middle-mouse-button drag to pan ───────────────────────────────────────
    let mmb = false, mmbSx = 0, mmbSy = 0, mmbPx = 0, mmbPy = 0;

    viewport.addEventListener('mousedown', (e) => {
        if (e.button !== 1) return;
        e.preventDefault(); // suppress browser scroll-icon / auto-scroll
        mmb = true;
        mmbSx = e.clientX; mmbSy = e.clientY;
        mmbPx = panX;      mmbPy = panY;
        viewport.style.cursor = 'grabbing';
    });

    document.addEventListener('mousemove', (e) => {
        if (!mmb) return;
        panX = mmbPx - (e.clientX - mmbSx) / zoom;
        panY = mmbPy - (e.clientY - mmbSy) / zoom;
        clampPan();
        applyTransform();
    });

    document.addEventListener('mouseup', (e) => {
        if (!mmb) return;
        if (e.button !== 1) return;
        mmb = false;
        viewport.style.cursor = '';
    });

    // Prevent the browser's native middle-click scroll-lock overlay
    viewport.addEventListener('auxclick', (e) => { if (e.button === 1) e.preventDefault(); });

    // ── Pointer helpers ───────────────────────────────────────────────────────
    // Canvas pixel = (screen coord − viewport origin) / zoom + pan offset
    function getPos(e) {
        const rect = viewport.getBoundingClientRect();
        const src  = e.touches ? e.touches[0] : e;
        return {
            x: (src.clientX - rect.left) / zoom + panX,
            y: (src.clientY - rect.top)  / zoom + panY,
        };
    }

    function getCanvasPos(e) {
        const rect = viewport.getBoundingClientRect();
        return {
            x: (e.clientX - rect.left) / zoom + panX,
            y: (e.clientY - rect.top)  / zoom + panY,
        };
    }

    // ── Tool config ───────────────────────────────────────────────────────────
    const DRAW_TOOLS = ['pen', 'pencil', 'marker', 'eraser'];

    function cfgCtx() {
        ctx.lineCap              = 'round';
        ctx.lineJoin             = 'round';
        ctx.globalCompositeOperation = 'source-over';
        if (tool === 'pen') {
            ctx.strokeStyle = color; ctx.lineWidth = size; ctx.globalAlpha = 1.0;
        } else if (tool === 'pencil') {
            ctx.strokeStyle = color; ctx.lineWidth = Math.max(1, size * 0.65); ctx.globalAlpha = 0.72;
        } else if (tool === 'marker') {
            ctx.strokeStyle = color; ctx.lineWidth = size * 2.8; ctx.globalAlpha = 0.32;
        } else if (tool === 'eraser') {
            ctx.strokeStyle = '#FFFFFF'; ctx.lineWidth = size * 3.5; ctx.globalAlpha = 1.0;
        }
    }

    // ── Drawing events ────────────────────────────────────────────────────────
    function onStart(e) {
        if (!isOwner || !DRAW_TOOLS.includes(tool)) return;
        if (e.button !== undefined && e.button !== 0) return; // only left click draws
        e.preventDefault();
        isDrawing = true;
        saveState();
        const { x, y } = getPos(e);
        lastX = x; lastY = y;
        cfgCtx();
        ctx.beginPath();
        ctx.arc(x, y, ctx.lineWidth / 2, 0, Math.PI * 2);
        ctx.fillStyle   = tool === 'eraser' ? '#FFFFFF' : color;
        ctx.globalAlpha = tool === 'marker' ? 0.32 : tool === 'pencil' ? 0.72 : 1.0;
        ctx.fill();
    }

    function onMove(e) {
        if (!isDrawing || !DRAW_TOOLS.includes(tool)) return;
        e.preventDefault();
        const { x, y } = getPos(e);
        cfgCtx();
        ctx.beginPath();
        ctx.moveTo(lastX, lastY);
        ctx.lineTo(x, y);
        ctx.stroke();
        lastX = x; lastY = y;
    }

    function onStop() {
        if (!isDrawing) return;
        isDrawing = false;
        ctx.globalAlpha = 1.0;
    }

    canvas.addEventListener('mousedown',  onStart);
    canvas.addEventListener('mousemove',  onMove);
    canvas.addEventListener('mouseup',    onStop);
    canvas.addEventListener('mouseleave', onStop);
    canvas.addEventListener('touchstart', onStart, { passive: false });
    canvas.addEventListener('touchmove',  onMove,  { passive: false });
    canvas.addEventListener('touchend',   onStop);

    // Click canvas to place sticky / text
    canvas.addEventListener('click', (e) => {
        if (!isOwner) return;
        if (tool === 'sticky') {
            const { x, y } = getCanvasPos(e);
            createElement('sticky', x - 100, y - 80, { color: '#fef08a', content: '' });
        } else if (tool === 'text') {
            const { x, y } = getCanvasPos(e);
            const el = createElement('text', x, y, { fontSize: textFontSize, color: color, content: '' });
            requestAnimationFrame(() => {
                document.getElementById(el.id)?.querySelector('[contenteditable]')?.focus();
            });
        }
    });

    // ── Tool buttons ──────────────────────────────────────────────────────────
    document.querySelectorAll('[data-tool]').forEach(btn => {
        btn.addEventListener('click', () => {
            tool = btn.dataset.tool;
            document.querySelectorAll('[data-tool]').forEach(b => {
                const active = b.dataset.tool === tool;
                b.classList.toggle('bg-[#E26B3D]', active);
                b.classList.toggle('text-white',     active);
                b.classList.toggle('text-slate-600', !active);
            });

            const cursors = { pen:'crosshair', pencil:'crosshair', marker:'crosshair', eraser:'cell', sticky:'copy', text:'text' };
            canvas.style.cursor = cursors[tool] || 'crosshair';

            const sizeEl = document.getElementById('sizeControls');
            const fontEl = document.getElementById('fontSizeControls');
            const isDraw = DRAW_TOOLS.includes(tool);

            if (sizeEl) sizeEl.classList.toggle('hidden', !isDraw);
            if (fontEl) {
                if (tool === 'text') { fontEl.classList.remove('hidden'); fontEl.classList.add('flex'); }
                else                 { fontEl.classList.add('hidden');    fontEl.classList.remove('flex'); }
            }
        });
    });

    // ── Color ─────────────────────────────────────────────────────────────────
    function setColor(hex) {
        color = hex;
        const btn    = document.getElementById('activeColorBtn');
        const picker = document.getElementById('colorPicker');
        if (btn)    btn.style.backgroundColor = hex;
        if (picker) picker.value = hex;
        if (tool === 'eraser') document.querySelector('[data-tool="pen"]')?.click();
    }

    document.querySelectorAll('[data-color]').forEach(s => s.addEventListener('click', () => setColor(s.dataset.color)));
    document.getElementById('colorPicker')?.addEventListener('input', e => setColor(e.target.value));

    // ── Color palette expand/collapse ─────────────────────────────────────────
    (function () {
        const btn      = document.getElementById('colorExpandBtn');
        const swatches = document.getElementById('colorSwatches');
        const chevron  = document.getElementById('colorChevron');
        if (!btn || !swatches) return;
        let open = false;
        btn.addEventListener('click', () => {
            open = !open;
            swatches.classList.toggle('hidden',  !open);
            swatches.classList.toggle('flex', open);
            chevron.style.transform = open ? 'rotate(90deg)' : '';
        });
    })();

    // ── Brush size ────────────────────────────────────────────────────────────
    const slider    = document.getElementById('brushSize');
    const sizeLabel = document.getElementById('sizeLabel');
    slider?.addEventListener('input', () => {
        size = parseInt(slider.value);
        if (sizeLabel) sizeLabel.textContent = size + 'px';
    });

    // ── Font size ─────────────────────────────────────────────────────────────
    document.getElementById('fontSizeSelect')?.addEventListener('change', e => {
        textFontSize = parseInt(e.target.value);
    });

    // ── Undo button ───────────────────────────────────────────────────────────
    document.getElementById('undoBtn')?.addEventListener('click', undo);

    // ── Clear confirm ─────────────────────────────────────────────────────────
    document.getElementById('clearConfirmBtn')?.addEventListener('click', () => {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        ctx.fillStyle = '#FFFFFF';
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        undoStack.length  = 0;
        saveState();
        elements          = [];
        elemLayer.innerHTML = '';
        nextElemId        = 1;
        document.getElementById('clearModal')?.classList.add('hidden');
    });

    // ── Save button ───────────────────────────────────────────────────────────
    document.getElementById('saveBtn')?.addEventListener('click', async () => {
        const btn  = document.getElementById('saveBtn');
        const orig = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 12a8 8 0 018-8"/></svg> Saving…';
        try {
            const res = await fetch('{{ route('whiteboards.save', $whiteboard) }}', {
                method:  'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                body:    JSON.stringify({ canvas_data: canvas.toDataURL('image/png'), elements_data: elements }),
            });
            if (!res.ok) throw new Error();
            btn.innerHTML        = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Saved';
            btn.style.background = '#10b981';
            setTimeout(() => { btn.innerHTML = orig; btn.disabled = false; btn.style.background = ''; }, 2500);
        } catch {
            btn.innerHTML = 'Error — try again';
            btn.disabled  = false;
        }
    });

    // ── Download ──────────────────────────────────────────────────────────────
    document.getElementById('downloadBtn')?.addEventListener('click', () => {
        const a    = document.createElement('a');
        a.download = '{{ Str::slug($whiteboard->title) }}.png';
        a.href     = canvas.toDataURL('image/png');
        a.click();
    });

    // ── Keyboard shortcuts ────────────────────────────────────────────────────
    document.addEventListener('keydown', e => {
        const inEditable = e.target.isContentEditable
            || e.target.tagName === 'TEXTAREA'
            || e.target.tagName === 'INPUT'
            || e.target.tagName === 'SELECT';

        if ((e.ctrlKey || e.metaKey) && e.key === 'z' && !e.shiftKey) { e.preventDefault(); undo(); return; }
        if ((e.ctrlKey || e.metaKey) && e.key === 's') { e.preventDefault(); document.getElementById('saveBtn')?.click(); return; }
        if ((e.ctrlKey || e.metaKey) && (e.key === '=' || e.key === '+')) { e.preventDefault(); setZoom(zoom + 0.1); return; }
        if ((e.ctrlKey || e.metaKey) && e.key === '-') { e.preventDefault(); setZoom(zoom - 0.1); return; }
        if ((e.ctrlKey || e.metaKey) && e.key === '0') { e.preventDefault(); zoom=1.0; panX=0; panY=0; applyTransform(); document.getElementById('zoomLabel').textContent='100%'; return; }

        if (!inEditable && !e.ctrlKey && !e.metaKey && !e.altKey) {
            const toolMap = { p:'pen', c:'pencil', m:'marker', e:'eraser', n:'sticky', t:'text' };
            if (toolMap[e.key]) document.querySelector('[data-tool="' + toolMap[e.key] + '"]')?.click();
        }
    });

    // ── Share search ──────────────────────────────────────────────────────────
    document.getElementById('shareSearch')?.addEventListener('input', function () {
        const q = this.value.toLowerCase().trim();
        let visible = 0;
        document.querySelectorAll('.share-user-row').forEach(row => {
            const match = !q || row.dataset.name.includes(q) || row.dataset.email.includes(q);
            row.style.display = match ? '' : 'none';
            if (match) visible++;
        });
        document.getElementById('shareNoResults')?.classList.toggle('hidden', visible > 0);
    });

    // ── Element system ────────────────────────────────────────────────────────

    const STICKY_PALETTES = [
        { bg: '#fef08a', hdr: '#fde047' },
        { bg: '#fda4af', hdr: '#fb7185' },
        { bg: '#93c5fd', hdr: '#60a5fa' },
        { bg: '#86efac', hdr: '#4ade80' },
        { bg: '#fdba74', hdr: '#fb923c' },
        { bg: '#c4b5fd', hdr: '#a78bfa' },
    ];

    function stickyHdr(bg) {
        return (STICKY_PALETTES.find(p => p.bg === bg) || STICKY_PALETTES[0]).hdr;
    }

    function createElement(type, x, y, opts) {
        const id = 'el-' + (nextElemId++);
        const el = Object.assign({ id, type, x, y }, opts || {});
        elements.push(el);
        renderElement(el);
        return el;
    }

    function removeElement(id) {
        elements = elements.filter(e => e.id !== id);
        document.getElementById(id)?.remove();
    }

    function renderElement(el) {
        document.getElementById(el.id)?.remove();
        let dom;
        if      (el.type === 'sticky') dom = makeStickyDOM(el);
        else if (el.type === 'text')   dom = makeTextDOM(el);
        else if (el.type === 'image')  dom = makeImageDOM(el);
        else if (el.type === 'file')   dom = makeFileDOM(el);
        else if (el.type === 'video')  dom = makeVideoDOM(el);
        if (!dom) return;
        dom.id             = el.id;
        dom.style.position = 'absolute';
        dom.style.left     = el.x + 'px';
        dom.style.top      = el.y + 'px';
        dom.style.zIndex   = '10';
        elemLayer.appendChild(dom);
        if (isOwner) makeDraggable(dom, el);
    }

    function loadElements(data) {
        if (!Array.isArray(data) || !data.length) return;
        elements = data;
        let maxId = 0;
        elements.forEach(el => {
            const n = parseInt((el.id || '').replace('el-', '')) || 0;
            if (n > maxId) maxId = n;
            renderElement(el);
        });
        nextElemId = maxId + 1;
    }

    // ── Sticky note ───────────────────────────────────────────────────────────
    function makeStickyDOM(el) {
        const bg  = el.color || '#fef08a';
        const hdr = stickyHdr(bg);

        const outer = document.createElement('div');
        outer.style.cssText = 'width:200px;min-height:168px;background:' + bg + ';border-radius:6px;box-shadow:2px 4px 14px rgba(0,0,0,0.18);pointer-events:all;display:flex;flex-direction:column;overflow:hidden';

        // Header (drag handle + color swatches + close)
        const header = document.createElement('div');
        header.className   = 'sticky-header';
        header.style.cssText = 'background:' + hdr + ';padding:6px 8px;display:flex;align-items:center;justify-content:space-between;cursor:move;user-select:none;flex-shrink:0;gap:6px';

        const swatchRow = document.createElement('div');
        swatchRow.style.cssText = 'display:flex;gap:3px;align-items:center';
        STICKY_PALETTES.forEach(p => {
            const s = document.createElement('span');
            s.style.cssText = 'width:11px;height:11px;border-radius:50%;background:' + p.bg + ';cursor:pointer;flex-shrink:0;border:' + (bg === p.bg ? '2px solid #111' : '1px solid rgba(0,0,0,0.2)') + ';display:inline-block';
            if (isOwner) {
                s.addEventListener('click', ev => {
                    ev.stopPropagation();
                    el.color = p.bg;
                    renderElement(el);
                    scheduleAutoSave();
                });
            }
            swatchRow.appendChild(s);
        });

        const closeBtn = document.createElement('button');
        closeBtn.innerHTML = '&times;';
        closeBtn.style.cssText = 'background:none;border:none;cursor:pointer;font-size:16px;line-height:1;color:#555;padding:0;pointer-events:all;flex-shrink:0';
        if (isOwner) closeBtn.addEventListener('click', ev => { ev.stopPropagation(); removeElement(el.id); });
        else         closeBtn.style.display = 'none';

        header.appendChild(swatchRow);
        header.appendChild(closeBtn);

        // Textarea
        const ta = document.createElement('textarea');
        ta.placeholder   = 'Type your note…';
        ta.value         = el.content || '';
        ta.readOnly      = !isOwner;
        ta.style.cssText = 'flex:1;background:transparent;border:none;outline:none;padding:8px 10px;font-size:13px;resize:none;font-family:inherit;min-height:120px;color:#1e293b;line-height:1.5;cursor:' + (isOwner ? 'text' : 'default');
        if (isOwner) {
            ta.addEventListener('input',     ev => { el.content = ev.target.value; scheduleAutoSave(); });
            ta.addEventListener('mousedown', ev => ev.stopPropagation());
        }

        outer.appendChild(header);
        outer.appendChild(ta);
        return outer;
    }

    // ── Text element ──────────────────────────────────────────────────────────
    function makeTextDOM(el) {
        const outer = document.createElement('div');
        outer.style.cssText = 'pointer-events:all;position:relative;display:inline-block;min-width:80px';

        // Dedicated drag handle so text content stays freely editable
        const dragHandle = document.createElement('div');
        dragHandle.className   = 'text-drag-handle';
        dragHandle.style.cssText = 'height:14px;cursor:grab;background:rgba(203,213,225,0.5);border-radius:4px 4px 0 0;'
            + 'display:flex;align-items:center;justify-content:center;user-select:none;gap:2px;padding:0 4px';
        dragHandle.innerHTML = '<svg width="16" height="6" fill="none" stroke="#94a3b8" stroke-width="1.5" stroke-linecap="round">'
            + '<line x1="0" y1="1.5" x2="16" y2="1.5"/><line x1="0" y1="4.5" x2="16" y2="4.5"/></svg>';

        const del = makeDelBtn(() => removeElement(el.id));
        if (isOwner) {
            outer.addEventListener('mouseenter', () => { dragHandle.style.background = 'rgba(203,213,225,0.7)'; del.style.display = 'flex'; });
            outer.addEventListener('mouseleave', () => {
                dragHandle.style.background = 'rgba(203,213,225,0.5)';
                if (document.activeElement !== editable) del.style.display = 'none';
            });
        } else {
            dragHandle.style.display = 'none';
        }

        const editable = document.createElement('div');
        editable.contentEditable = isOwner ? 'true' : 'false';
        editable.style.cssText   = 'font-size:' + (el.fontSize || 18) + 'px;color:' + (el.color || '#000') + ';outline:none;min-width:60px;min-height:1.3em;white-space:pre-wrap;padding:4px 6px;font-family:inherit;line-height:1.45;cursor:' + (isOwner ? 'text' : 'default');
        editable.textContent     = el.content || '';

        if (isOwner) {
            if (!el.content) editable.style.borderBottom = '1.5px dashed #94a3b8';
            editable.addEventListener('input', ev => {
                el.content = ev.target.textContent;
                editable.style.borderBottom = el.content ? 'none' : '1.5px dashed #94a3b8';
                scheduleAutoSave();
            });
            editable.addEventListener('mousedown', ev => ev.stopPropagation());
            editable.addEventListener('focus',     () => del.style.display = 'flex');
            editable.addEventListener('blur',      () => setTimeout(() => { del.style.display = 'none'; }, 150));
        }

        outer.appendChild(dragHandle);
        outer.appendChild(del);
        outer.appendChild(editable);
        return outer;
    }

    // ── Image element ─────────────────────────────────────────────────────────
    function makeImageDOM(el) {
        const w = el.width  || 240;
        const h = el.height || 180;

        const outer = document.createElement('div');
        outer.style.cssText = 'width:' + w + 'px;height:' + h + 'px;pointer-events:all;position:relative;box-shadow:0 2px 10px rgba(0,0,0,0.15);border-radius:6px;overflow:visible;cursor:move';

        const img = document.createElement('img');
        img.src           = el.src;
        img.draggable     = false;
        img.style.cssText = 'width:100%;height:100%;object-fit:contain;display:block;border-radius:6px;user-select:none';

        const del = makeDelBtn(() => removeElement(el.id));
        if (isOwner) {
            outer.addEventListener('mouseenter', () => del.style.display = 'flex');
            outer.addEventListener('mouseleave', () => del.style.display = 'none');
        }

        outer.appendChild(img);
        if (isOwner) {
            outer.appendChild(del);
            outer.appendChild(makeResizeHandle(outer, el, w, h));
        }
        return outer;
    }

    // ── File card ─────────────────────────────────────────────────────────────
    function makeFileDOM(el) {
        const outer = document.createElement('div');
        outer.style.cssText = 'pointer-events:all;background:white;border:1.5px solid #e2e8f0;border-radius:10px;padding:10px 14px;display:flex;align-items:center;gap:10px;box-shadow:0 2px 8px rgba(0,0,0,0.08);min-width:200px;max-width:260px;position:relative;cursor:move';

        const icon = document.createElement('div');
        icon.style.cssText = 'width:38px;height:38px;background:#f1f5f9;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0';
        icon.innerHTML     = fileIconSVG(el.mime || '');

        const info = document.createElement('div');
        info.style.cssText = 'flex:1;min-width:0';
        info.innerHTML     = '<div style="font-size:12px;font-weight:600;color:#1e293b;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">' + escHtml(el.filename || 'File') + '</div><div style="font-size:11px;color:#94a3b8;margin-top:2px">' + fmtBytes(el.size || 0) + '</div>';

        const openLink = document.createElement('a');
        openLink.href         = el.src;
        openLink.target       = '_blank';
        openLink.style.cssText = 'font-size:11px;color:#3b82f6;text-decoration:none;flex-shrink:0;pointer-events:all;white-space:nowrap';
        openLink.textContent  = 'Open';
        openLink.addEventListener('mousedown', ev => ev.stopPropagation());
        openLink.addEventListener('click',     ev => ev.stopPropagation());

        const del = makeDelBtn(() => removeElement(el.id));
        if (isOwner) {
            outer.addEventListener('mouseenter', () => del.style.display = 'flex');
            outer.addEventListener('mouseleave', () => del.style.display = 'none');
        }

        outer.appendChild(icon);
        outer.appendChild(info);
        outer.appendChild(openLink);
        if (isOwner) outer.appendChild(del);
        return outer;
    }

    // ── Video element ─────────────────────────────────────────────────────────
    function makeVideoDOM(el) {
        const w = el.width  || 320;
        const h = el.height || 200;

        const outer = document.createElement('div');
        outer.style.cssText = 'width:' + w + 'px;pointer-events:all;background:#0f172a;border-radius:8px;overflow:visible;position:relative;box-shadow:0 4px 14px rgba(0,0,0,0.25)';

        const dragBar = document.createElement('div');
        dragBar.className   = 'drag-handle';
        dragBar.style.cssText = 'background:#1e293b;padding:6px 10px;display:flex;align-items:center;gap:6px;border-radius:8px 8px 0 0;cursor:move;user-select:none';
        dragBar.innerHTML   = '<svg style="width:13px;height:13px;flex-shrink:0" fill="none" stroke="#64748b" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><span style="font-size:11px;color:#64748b;flex:1;min-width:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">' + escHtml(el.filename || 'Video') + '</span>';

        const video = document.createElement('video');
        video.src          = el.src;
        video.controls     = true;
        video.style.cssText = 'width:100%;height:' + h + 'px;display:block;border-radius:0 0 8px 8px;background:#000';
        video.addEventListener('mousedown', ev => ev.stopPropagation());

        const del = makeDelBtn(() => removeElement(el.id));
        if (isOwner) {
            outer.addEventListener('mouseenter', () => del.style.display = 'flex');
            outer.addEventListener('mouseleave', () => del.style.display = 'none');
            outer.appendChild(makeResizeHandle(outer, el, w, h, true));
        }

        outer.appendChild(dragBar);
        outer.appendChild(video);
        if (isOwner) outer.appendChild(del);
        return outer;
    }

    // ── Shared helpers ────────────────────────────────────────────────────────
    function makeDelBtn(onClick) {
        const btn = document.createElement('button');
        btn.innerHTML     = '&times;';
        btn.style.cssText = 'position:absolute;top:-9px;right:-9px;width:20px;height:20px;background:#ef4444;color:white;border:none;border-radius:50%;cursor:pointer;font-size:13px;line-height:1;display:none;align-items:center;justify-content:center;z-index:30;pointer-events:all;box-shadow:0 1px 4px rgba(0,0,0,0.25)';
        btn.addEventListener('click', ev => { ev.stopPropagation(); onClick(); });
        return btn;
    }

    function makeResizeHandle(dom, el, initW, initH, videoMode) {
        const handle = document.createElement('div');
        handle.style.cssText = 'position:absolute;bottom:-6px;right:-6px;width:14px;height:14px;background:#3b82f6;border-radius:3px;cursor:se-resize;z-index:30;pointer-events:all';
        handle.addEventListener('mousedown', ev => {
            ev.stopPropagation();
            ev.preventDefault();
            const sx = ev.clientX, sy = ev.clientY;
            const ow = el.width  || initW;
            const oh = el.height || initH;
            const onMv = ev2 => {
                el.width  = Math.max(80,  ow + (ev2.clientX - sx) / zoom);
                el.height = Math.max(60,  oh + (ev2.clientY - sy) / zoom);
                dom.style.width = el.width + 'px';
                if (!videoMode) dom.style.height = el.height + 'px';
                const media = dom.querySelector('video, img');
                if (media) media.style.height = el.height + 'px';
            };
            const onUp = () => { document.removeEventListener('mousemove', onMv); document.removeEventListener('mouseup', onUp); scheduleAutoSave(); };
            document.addEventListener('mousemove', onMv);
            document.addEventListener('mouseup',   onUp);
        });
        return handle;
    }

    function escHtml(s) {
        return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    function fmtBytes(b) {
        if (!b)         return '';
        if (b < 1024)   return b + ' B';
        if (b < 1048576) return (b / 1024).toFixed(1) + ' KB';
        return (b / 1048576).toFixed(1) + ' MB';
    }

    function fileIconSVG(mime) {
        const c = mime.includes('pdf') ? '#ef4444'
                : (mime.includes('word') || mime.includes('document')) ? '#3b82f6'
                : (mime.includes('sheet') || mime.includes('excel'))   ? '#22c55e'
                : (mime.includes('presentation') || mime.includes('powerpoint')) ? '#f97316'
                : '#6b7280';
        return '<svg style="width:20px;height:20px" fill="none" stroke="' + c + '" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>';
    }

    // ── Dragging ──────────────────────────────────────────────────────────────
    function makeDraggable(dom, el) {
        let dragging = false, sx, sy, ox, oy;

        const handle = dom.querySelector('.sticky-header, .drag-handle, .text-drag-handle') || dom;

        handle.addEventListener('mousedown', ev => {
            if (ev.target.tagName === 'TEXTAREA') return;
            if (ev.target.isContentEditable)       return;
            if (ev.target.closest('button, a, input')) return;
            ev.preventDefault();
            ev.stopPropagation();
            dragging = true;
            sx = ev.clientX; sy = ev.clientY;
            ox = el.x;       oy = el.y;
            dom.style.zIndex = '100';
        });

        document.addEventListener('mousemove', ev => {
            if (!dragging) return;
            el.x = ox + (ev.clientX - sx) / zoom;
            el.y = oy + (ev.clientY - sy) / zoom;
            dom.style.left = el.x + 'px';
            dom.style.top  = el.y + 'px';
        });

        document.addEventListener('mouseup', () => {
            if (!dragging) return;
            dragging         = false;
            dom.style.zIndex = '10';
            scheduleAutoSave();
        });
    }

    // ── Attach ────────────────────────────────────────────────────────────────
    document.getElementById('attachBtn')?.addEventListener('click', () => {
        const input   = document.createElement('input');
        input.type    = 'file';
        input.accept  = 'image/*,video/*,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.zip,.rar,.csv';
        input.onchange = async ev => {
            const file = ev.target.files[0];
            if (!file) return;

            // Place at the visible viewport centre, accounting for current pan and zoom
            const cx = panX + (viewport.clientWidth  / zoom) / 2 - 120;
            const cy = panY + (viewport.clientHeight / zoom) / 2 - 100;
            const toast = showToast('Uploading ' + file.name + '…');

            const fd = new FormData();
            fd.append('file',   file);
            fd.append('_token', document.querySelector('meta[name=csrf-token]').content);

            try {
                const res  = await fetch('{{ route('whiteboards.attach', $whiteboard) }}', { method: 'POST', body: fd });
                const data = await res.json();
                toast.remove();

                if (data.mime && data.mime.startsWith('image/')) {
                    createElement('image', cx, cy, { src: data.url, filename: data.filename, width: 240, height: 180 });
                } else if (data.mime && data.mime.startsWith('video/')) {
                    createElement('video', cx, cy, { src: data.url, filename: data.filename, width: 320, height: 200 });
                } else {
                    createElement('file', cx, cy, { src: data.url, filename: data.filename, size: data.size, mime: data.mime });
                }
            } catch {
                toast.remove();
                showToast('Upload failed. Please try again.', true);
            }
        };
        input.click();
    });

    function showToast(msg, isError) {
        const t = document.createElement('div');
        t.style.cssText = 'position:fixed;bottom:28px;left:50%;transform:translateX(-50%);background:' + (isError ? '#ef4444' : '#1e293b') + ';color:white;padding:9px 18px;border-radius:8px;font-size:13px;z-index:9999;pointer-events:none;box-shadow:0 4px 12px rgba(0,0,0,0.2)';
        t.textContent = msg;
        document.body.appendChild(t);
        if (isError) setTimeout(() => t.remove(), 4000);
        return t;
    }

    // ── Init ──────────────────────────────────────────────────────────────────
    document.body.style.overflow = 'hidden'; // prevent page scroll; all scrolling is canvas pan
    initCanvas();
    loadElements(@json($elementsData ?? []));

})();

@if($isOwner)
// ── Inline board title rename ─────────────────────────────────────────────
(function () {
    const display = document.getElementById('boardTitleDisplay');
    const input   = document.getElementById('boardTitleInput');
    if (!display || !input) return;

    display.addEventListener('click', () => {
        input.value = display.textContent.trim();
        display.classList.add('hidden');
        input.classList.remove('hidden');
        input.select();
    });

    function save() {
        const val = input.value.trim();
        if (!val) { cancel(); return; }
        fetch('{{ route('whiteboards.rename', $whiteboard) }}', {
            method:  'PATCH',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
            body:    JSON.stringify({ title: val }),
        }).then(r => r.json()).then(d => {
            if (d.success) {
                display.textContent = d.title;
                document.title      = d.title + ' — Admin';
            }
        }).finally(cancel);
    }

    function cancel() {
        input.classList.add('hidden');
        display.classList.remove('hidden');
    }

    input.addEventListener('blur',    save);
    input.addEventListener('keydown', e => {
        if (e.key === 'Enter')  { e.preventDefault(); input.blur(); }
        if (e.key === 'Escape') { input.value = display.textContent.trim(); input.blur(); }
    });
})();
@endif
</script>
@endsection

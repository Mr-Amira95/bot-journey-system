<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $brd->title }} — Bot Journey</title>
    <link rel="icon" type="image/png" href="{{ asset('icon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Spectral:ital,wght@0,400;0,600;1,400;1,600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-stone-50 font-sans antialiased">

    @php
        $statusStyles = [
            'pending'  => 'bg-amber-100 text-amber-700',
            'approved' => 'bg-emerald-100 text-emerald-700',
            'rejected' => 'bg-red-100 text-red-700',
        ];
        $priorityColors = [
            'low'    => 'bg-slate-100 text-slate-600',
            'medium' => 'bg-blue-100 text-blue-700',
            'high'   => 'bg-red-100 text-red-700',
        ];
        $kpiLines = $brd->kpis ? array_values(array_filter(array_map('trim', explode("\n", $brd->kpis)), fn ($l) => $l !== '')) : [];
    @endphp

    {{-- Top bar --}}
    <div class="bg-[#0f1b3d]">
        <div class="max-w-4xl mx-auto px-6 py-5 flex items-center gap-3">
            <svg width="26" height="31" viewBox="0 0 70 85" fill="none" xmlns="http://www.w3.org/2000/svg">
                <line x1="35" y1="19" x2="8" y2="78" stroke="#F2EEE5" stroke-width="11" stroke-linecap="round" opacity="0.8"/>
                <line x1="35" y1="19" x2="62" y2="78" stroke="#F2EEE5" stroke-width="11" stroke-linecap="round" opacity="0.8"/>
                <circle cx="35" cy="17" r="14" fill="#E26B3D"/>
            </svg>
            <div>
                <p class="text-[#F2EEE5] text-sm font-semibold italic tracking-tight" style="font-family: 'Spectral', serif;">Bot Journey</p>
                <p class="text-[#F2EEE5]/50 text-[10px] font-mono tracking-widest uppercase">Shared Document</p>
            </div>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-6 py-8 space-y-6">

        {{-- Header card --}}
        <div class="bg-white rounded-xl border border-slate-200 p-6">
            <div class="flex items-start justify-between gap-4 mb-2">
                <h1 class="text-xl font-semibold text-slate-800">{{ $brd->title }}</h1>
                <div class="flex items-center gap-2 shrink-0">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $statusStyles[$brd->status->value] ?? 'bg-slate-100 text-slate-700' }}">
                        {{ ucfirst($brd->status->value) }}
                    </span>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $priorityColors[$brd->priority->value] ?? 'bg-slate-100 text-slate-600' }}">
                        {{ ucfirst($brd->priority->value) }} priority
                    </span>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-slate-500 mb-5">
                @if($brd->project)
                <span>{{ $brd->project->name }}</span>
                @endif
                @if($brd->department)
                <span class="text-slate-300">·</span>
                <span>{{ $brd->department }}</span>
                @endif
                @if($brd->creator)
                <span class="text-slate-300">·</span>
                <span>Prepared by {{ $brd->creator->name }}</span>
                @endif
                <span class="text-slate-300">·</span>
                <span>{{ $brd->created_at->format('d M Y') }}</span>
            </div>

            <div>
                <h2 class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Description</h2>
                <p class="text-sm text-slate-700 whitespace-pre-line leading-relaxed">{{ $brd->description }}</p>
            </div>
        </div>

        {{-- Objective & Scope --}}
        @if($brd->objective || $brd->scope)
        <div class="bg-white rounded-xl border border-slate-200 p-6 space-y-5">
            @if($brd->objective)
            <div>
                <h2 class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Business Objective</h2>
                <p class="text-sm text-slate-700 whitespace-pre-line leading-relaxed">{{ $brd->objective }}</p>
            </div>
            @endif
            @if($brd->scope)
            <div class="{{ $brd->objective ? 'pt-5 border-t border-slate-100' : '' }}">
                <h2 class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Scope</h2>
                <p class="text-sm text-slate-700 whitespace-pre-line leading-relaxed">{{ $brd->scope }}</p>
            </div>
            @endif
        </div>
        @endif

        {{-- As-Is / To-Be comparison --}}
        @if($brd->as_is_workflow || $brd->as_is_pain_points || $brd->as_is_existing_systems || $brd->to_be_workflow || $brd->to_be_benefits)
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @if($brd->as_is_workflow || $brd->as_is_pain_points || $brd->as_is_existing_systems)
            <div class="bg-white rounded-xl border border-slate-200 border-l-4 border-l-slate-300 p-6 space-y-4">
                <h2 class="text-xs font-semibold text-slate-500 uppercase tracking-wide">
                    Current Process <span class="normal-case font-normal text-slate-400">("As-Is")</span>
                </h2>
                @if($brd->as_is_workflow)
                <div>
                    <p class="text-xs font-medium text-slate-500 mb-1">Workflow</p>
                    <p class="text-sm text-slate-700 whitespace-pre-line leading-relaxed">{{ $brd->as_is_workflow }}</p>
                </div>
                @endif
                @if($brd->as_is_pain_points)
                <div>
                    <p class="text-xs font-medium text-slate-500 mb-1">Pain Points</p>
                    <p class="text-sm text-slate-700 whitespace-pre-line leading-relaxed">{{ $brd->as_is_pain_points }}</p>
                </div>
                @endif
                @if($brd->as_is_existing_systems)
                <div>
                    <p class="text-xs font-medium text-slate-500 mb-1">Existing Systems / Tools</p>
                    <p class="text-sm text-slate-700 whitespace-pre-line leading-relaxed">{{ $brd->as_is_existing_systems }}</p>
                </div>
                @endif
            </div>
            @endif

            @if($brd->to_be_workflow || $brd->to_be_benefits)
            <div class="bg-white rounded-xl border border-slate-200 border-l-4 border-l-emerald-300 p-6 space-y-4">
                <h2 class="text-xs font-semibold text-slate-500 uppercase tracking-wide">
                    Proposed Process <span class="normal-case font-normal text-slate-400">("To-Be")</span>
                </h2>
                @if($brd->to_be_workflow)
                <div>
                    <p class="text-xs font-medium text-slate-500 mb-1">Workflow</p>
                    <p class="text-sm text-slate-700 whitespace-pre-line leading-relaxed">{{ $brd->to_be_workflow }}</p>
                </div>
                @endif
                @if($brd->to_be_benefits)
                <div>
                    <p class="text-xs font-medium text-slate-500 mb-1">Benefits</p>
                    <p class="text-sm text-slate-700 whitespace-pre-line leading-relaxed">{{ $brd->to_be_benefits }}</p>
                </div>
                @endif
            </div>
            @endif
        </div>
        @endif

        {{-- KPIs --}}
        @if(!empty($kpiLines))
        <div class="bg-white rounded-xl border border-slate-200 p-6">
            <h2 class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-3">KPIs</h2>
            <ul class="space-y-2">
                @foreach($kpiLines as $line)
                <li class="flex items-start gap-2 text-sm text-slate-700">
                    <svg class="w-4 h-4 text-emerald-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>{{ $line }}</span>
                </li>
                @endforeach
            </ul>
        </div>
        @endif

        {{-- Stakeholders --}}
        @if($brd->stakeholders->isNotEmpty())
        <div class="bg-white rounded-xl border border-slate-200 p-6">
            <h2 class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-3">Stakeholders</h2>
            <div class="divide-y divide-slate-100">
                @foreach($brd->stakeholders as $sh)
                <div class="flex items-start gap-3 py-3">
                    <div class="w-9 h-9 rounded-full bg-[#E26B3D]/10 flex items-center justify-center shrink-0 text-xs font-semibold text-[#E26B3D]">
                        {{ strtoupper(substr($sh->name, 0, 1)) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <p class="text-sm font-medium text-slate-800">{{ $sh->name }}</p>
                            @if($sh->role)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs bg-blue-50 text-blue-700">{{ $sh->role }}</span>
                            @endif
                            @if($sh->department)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs bg-slate-100 text-slate-600">{{ $sh->department }}</span>
                            @endif
                        </div>
                        @if($sh->responsibility)
                        <p class="text-sm text-slate-500 mt-1">{{ $sh->responsibility }}</p>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Attachments --}}
        @if($brd->attachments->isNotEmpty())
        <div class="bg-white rounded-xl border border-slate-200 p-6">
            <h2 class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-3">Attachments</h2>
            <div class="divide-y divide-slate-100">
                @foreach($brd->attachments as $attachment)
                <div class="flex items-center justify-between py-3 gap-3">
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-slate-800 truncate">{{ $attachment->file_name }}</p>
                        <p class="text-xs text-slate-400 truncate">{{ number_format($attachment->file_size / 1024, 1) }} KB</p>
                    </div>
                    <a href="{{ Illuminate\Support\Facades\Storage::disk('public')->url($attachment->file_path) }}" target="_blank" download
                       class="shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-slate-600 hover:text-slate-800 bg-slate-100 hover:bg-slate-200 transition-colors">
                        Download
                    </a>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <p class="text-center text-xs text-slate-400 pt-2">
            This is a shared, read-only view generated by {{ config('app.name') }}.
        </p>
    </div>

</body>
</html>

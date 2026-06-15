<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Bot Journey') — Admin</title>
    <link rel="icon" type="image/png" href="{{ asset('icon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Spectral:ital,wght@0,400;0,600;1,400;1,600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#0f1b3d] flex items-center justify-center p-4 font-sans antialiased">

    <div class="w-full max-w-md">
        {{-- Logo --}}
        <div class="mb-8 text-center">
            <div class="inline-flex flex-col items-center gap-3">
                {{-- BotJourney Icon --}}
                <svg width="52" height="62" viewBox="0 0 70 85" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <line x1="35" y1="19" x2="8" y2="78" stroke="#F2EEE5" stroke-width="11" stroke-linecap="round" opacity="0.8"/>
                    <line x1="35" y1="19" x2="62" y2="78" stroke="#F2EEE5" stroke-width="11" stroke-linecap="round" opacity="0.8"/>
                    <circle cx="35" cy="17" r="14" fill="#E26B3D"/>
                </svg>
                <div>
                    <h1 class="text-3xl font-semibold italic text-[#F2EEE5] tracking-tight leading-none" style="font-family: 'Spectral', serif;">Bot Journey</h1>
                    <p class="text-[#F2EEE5]/50 text-xs mt-2 font-mono tracking-widest uppercase">Admin Panel</p>
                </div>
            </div>
        </div>

        {{-- Card --}}
        <div class="bg-white rounded-2xl shadow-2xl p-8">
            @yield('content')
        </div>
    </div>

</body>
</html>

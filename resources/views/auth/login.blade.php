@extends('layouts.auth')

@section('title', 'Sign In')

@section('content')
<h2 class="text-xl font-semibold text-slate-800 mb-1">Welcome back</h2>
<p class="text-sm text-slate-500 mb-6 font-mono">Sign in to your admin account</p>

<form method="POST" action="{{ route('login.post') }}" class="space-y-4">
    @csrf

    {{-- Email --}}
    <div>
        <label for="email" class="block text-xs font-mono font-medium text-slate-600 mb-1.5 uppercase tracking-wider">Email address</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email"
               class="w-full rounded-lg border px-3.5 py-2.5 text-sm text-slate-900 shadow-sm font-mono
                      placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-[#E26B3D] focus:border-[#E26B3D]
                      {{ $errors->has('email') ? 'border-red-400 bg-red-50' : 'border-slate-300 bg-white' }}"
               placeholder="admin@admin.com">
        @error('email')
            <p class="mt-1.5 text-xs text-red-600 font-mono">{{ $message }}</p>
        @enderror
    </div>

    {{-- Password --}}
    <div>
        <div class="flex items-center justify-between mb-1.5">
            <label for="password" class="block text-xs font-mono font-medium text-slate-600 uppercase tracking-wider">Password</label>
            <a href="{{ route('password.request') }}" class="text-xs text-[#E26B3D] hover:text-[#c8602a] font-mono font-medium transition-colors">
                Forgot password?
            </a>
        </div>
        <input id="password" type="password" name="password" required autocomplete="current-password"
               class="w-full rounded-lg border px-3.5 py-2.5 text-sm text-slate-900 shadow-sm font-mono
                      placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-[#E26B3D] focus:border-[#E26B3D]
                      {{ $errors->has('password') ? 'border-red-400 bg-red-50' : 'border-slate-300 bg-white' }}"
               placeholder="••••••••">
        @error('password')
            <p class="mt-1.5 text-xs text-red-600 font-mono">{{ $message }}</p>
        @enderror
    </div>

    {{-- Remember me --}}
    <div class="flex items-center gap-2">
        <input id="remember" type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}
               class="w-4 h-4 rounded border-slate-300 text-[#E26B3D] focus:ring-[#E26B3D]">
        <label for="remember" class="text-sm text-slate-600 font-mono">Keep me signed in</label>
    </div>

    {{-- Submit --}}
    <button type="submit"
            class="w-full rounded-lg bg-[#E26B3D] px-4 py-2.5 text-sm font-mono font-medium text-white shadow-sm
                   hover:bg-[#c8602a] focus:outline-none focus:ring-2 focus:ring-[#E26B3D] focus:ring-offset-2
                   transition-colors mt-2">
        Sign in
    </button>
</form>
@endsection

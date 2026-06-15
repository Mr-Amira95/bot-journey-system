@extends('layouts.auth')

@section('title', 'Reset Password')

@section('content')
<h2 class="text-xl font-semibold text-slate-800 mb-1">Set new password</h2>
<p class="text-sm text-slate-500 mb-6 font-mono">Create a strong password for your account.</p>

<form method="POST" action="{{ route('password.update') }}" class="space-y-4">
    @csrf

    <input type="hidden" name="token" value="{{ $token }}">

    {{-- Email --}}
    <div>
        <label for="email" class="block text-xs font-mono font-medium text-slate-600 mb-1.5 uppercase tracking-wider">Email address</label>
        <input id="email" type="email" name="email" value="{{ old('email', $email) }}" required autofocus autocomplete="email"
               class="w-full rounded-lg border px-3.5 py-2.5 text-sm text-slate-900 shadow-sm font-mono
                      placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-[#E26B3D] focus:border-[#E26B3D]
                      {{ $errors->has('email') ? 'border-red-400 bg-red-50' : 'border-slate-300 bg-white' }}"
               placeholder="you@company.com">
        @error('email')
            <p class="mt-1.5 text-xs text-red-600 font-mono">{{ $message }}</p>
        @enderror
    </div>

    {{-- Password --}}
    <div>
        <label for="password" class="block text-xs font-mono font-medium text-slate-600 mb-1.5 uppercase tracking-wider">New Password</label>
        <input id="password" type="password" name="password" required autocomplete="new-password"
               class="w-full rounded-lg border px-3.5 py-2.5 text-sm text-slate-900 shadow-sm font-mono
                      placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-[#E26B3D] focus:border-[#E26B3D]
                      {{ $errors->has('password') ? 'border-red-400 bg-red-50' : 'border-slate-300 bg-white' }}"
               placeholder="••••••••">
        @error('password')
            <p class="mt-1.5 text-xs text-red-600 font-mono">{{ $message }}</p>
        @enderror
    </div>

    {{-- Confirm Password --}}
    <div>
        <label for="password_confirmation" class="block text-xs font-mono font-medium text-slate-600 mb-1.5 uppercase tracking-wider">Confirm Password</label>
        <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
               class="w-full rounded-lg border px-3.5 py-2.5 text-sm text-slate-900 shadow-sm font-mono
                      placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-[#E26B3D] focus:border-[#E26B3D]
                      border-slate-300 bg-white"
               placeholder="••••••••">
    </div>

    {{-- Submit --}}
    <button type="submit"
            class="w-full rounded-lg bg-[#E26B3D] px-4 py-2.5 text-sm font-mono font-medium text-white shadow-sm
                   hover:bg-[#c8602a] focus:outline-none focus:ring-2 focus:ring-[#E26B3D] focus:ring-offset-2
                   transition-colors mt-2">
        Reset Password
    </button>
</form>

<div class="mt-5 text-center">
    <a href="{{ route('login') }}" class="text-sm text-slate-500 hover:text-[#E26B3D] transition-colors font-mono">
        ← Back to sign in
    </a>
</div>
@endsection

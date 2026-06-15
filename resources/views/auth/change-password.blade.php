@extends('layouts.auth')

@section('title', 'Change Password')

@section('content')
<h2 class="text-xl font-semibold text-slate-800 mb-1">Change your password</h2>
<p class="text-sm text-slate-500 mb-6 font-mono">You must set a new password before continuing.</p>

<form method="POST" action="{{ route('password.change.post') }}" class="space-y-4">
    @csrf

    {{-- Current Password --}}
    <div>
        <label for="current_password" class="block text-xs font-mono font-medium text-slate-600 mb-1.5 uppercase tracking-wider">Current password</label>
        <input id="current_password" type="password" name="current_password" required autocomplete="current-password"
               class="w-full rounded-lg border px-3.5 py-2.5 text-sm text-slate-900 shadow-sm font-mono
                      placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-[#E26B3D] focus:border-[#E26B3D]
                      {{ $errors->has('current_password') ? 'border-red-400 bg-red-50' : 'border-slate-300 bg-white' }}"
               placeholder="••••••••">
        @error('current_password')
            <p class="mt-1.5 text-xs text-red-600 font-mono">{{ $message }}</p>
        @enderror
    </div>

    {{-- New Password --}}
    <div>
        <label for="password" class="block text-xs font-mono font-medium text-slate-600 mb-1.5 uppercase tracking-wider">New password</label>
        <input id="password" type="password" name="password" required autocomplete="new-password"
               class="w-full rounded-lg border px-3.5 py-2.5 text-sm text-slate-900 shadow-sm font-mono
                      placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-[#E26B3D] focus:border-[#E26B3D]
                      {{ $errors->has('password') ? 'border-red-400 bg-red-50' : 'border-slate-300 bg-white' }}"
               placeholder="••••••••">
        @error('password')
            <p class="mt-1.5 text-xs text-red-600 font-mono">{{ $message }}</p>
        @enderror
    </div>

    {{-- Confirm New Password --}}
    <div>
        <label for="password_confirmation" class="block text-xs font-mono font-medium text-slate-600 mb-1.5 uppercase tracking-wider">Confirm new password</label>
        <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
               class="w-full rounded-lg border px-3.5 py-2.5 text-sm text-slate-900 shadow-sm font-mono
                      placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-[#E26B3D] focus:border-[#E26B3D]
                      {{ $errors->has('password_confirmation') ? 'border-red-400 bg-red-50' : 'border-slate-300 bg-white' }}"
               placeholder="••••••••">
        @error('password_confirmation')
            <p class="mt-1.5 text-xs text-red-600 font-mono">{{ $message }}</p>
        @enderror
    </div>

    {{-- Submit --}}
    <button type="submit"
            class="w-full rounded-lg bg-[#E26B3D] px-4 py-2.5 text-sm font-mono font-medium text-white shadow-sm
                   hover:bg-[#c8602a] focus:outline-none focus:ring-2 focus:ring-[#E26B3D] focus:ring-offset-2
                   transition-colors mt-2">
        Set new password
    </button>
</form>

<form method="POST" action="{{ route('logout') }}" class="mt-4 text-center">
    @csrf
    <button type="submit" class="text-xs text-slate-500 hover:text-slate-700 font-mono transition-colors">
        Sign out instead
    </button>
</form>
@endsection

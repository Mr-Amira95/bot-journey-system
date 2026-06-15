@extends('layouts.auth')

@section('title', 'Forgot Password')

@section('content')
<h2 class="text-xl font-semibold text-slate-800 mb-1">Reset your password</h2>
<p class="text-sm text-slate-500 mb-6 font-mono">Enter your email and we'll send you a reset link.</p>

@if(session('status'))
    <div x-data="{ show: true }" x-show="show"
         class="mb-4 flex items-center gap-3 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
        <svg class="h-4 w-4 shrink-0 text-green-500" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        <span class="font-mono">{{ session('status') }}</span>
    </div>
@endif

<form method="POST" action="{{ route('password.email') }}" class="space-y-4">
    @csrf

    <div>
        <label for="email" class="block text-xs font-mono font-medium text-slate-600 mb-1.5 uppercase tracking-wider">Email address</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
               class="w-full rounded-lg border px-3.5 py-2.5 text-sm text-slate-900 shadow-sm font-mono
                      placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-[#E26B3D] focus:border-[#E26B3D]
                      {{ $errors->has('email') ? 'border-red-400 bg-red-50' : 'border-slate-300 bg-white' }}"
               placeholder="you@company.com">
        @error('email')
            <p class="mt-1.5 text-xs text-red-600 font-mono">{{ $message }}</p>
        @enderror
    </div>

    <button type="submit"
            class="w-full rounded-lg bg-[#E26B3D] px-4 py-2.5 text-sm font-mono font-medium text-white shadow-sm
                   hover:bg-[#c8602a] focus:outline-none focus:ring-2 focus:ring-[#E26B3D] focus:ring-offset-2
                   transition-colors">
        Send reset link
    </button>
</form>

<div class="mt-5 text-center">
    <a href="{{ route('login') }}" class="text-sm text-slate-500 hover:text-[#E26B3D] transition-colors font-mono">
        ← Back to sign in
    </a>
</div>
@endsection

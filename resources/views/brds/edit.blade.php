@extends('layouts.app')

@section('title', 'Edit BRD')
@section('page-title', 'Edit BRD')

@section('header-actions')
    <a href="{{ route('brds.show', $brd) }}"
       class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-stone-50 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Back
    </a>
@endsection

@section('content')
<div class="max-w-4xl">
    @include('brds._form', ['mode' => 'edit', 'brd' => $brd, 'projects' => $projects])
</div>
@endsection

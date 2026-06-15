@extends('layouts.app')

@section('title', 'Email Templates')
@section('page-title', 'Email Templates')

@section('content')
@include('components.flash-messages')

<div class="space-y-6" x-data="{ activeTab: '{{ old('_tab', 'job_offer') }}' }">

    {{-- Tabs --}}
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="flex border-b border-slate-200">
            @foreach($types as $type => $label)
            <button @click="activeTab = '{{ $type }}'"
                    :class="activeTab === '{{ $type }}'
                        ? 'border-b-2 border-[#E26B3D] text-[#E26B3D] bg-orange-50/40'
                        : 'text-slate-500 hover:text-slate-700 hover:bg-stone-50'"
                    class="px-6 py-3.5 text-sm font-mono font-medium transition-colors relative">
                {{ $label }}
            </button>
            @endforeach
        </div>

        @php
            $canEditTemplates = auth()->user()->hasPermission('edit_email_templates');
        @endphp
        @foreach($templates as $type => $template)
        <div x-show="activeTab === '{{ $type }}'" style="display: none">
            <form action="{{ route('email-templates.update', $type) }}" method="POST" class="p-6 space-y-5">
                @csrf
                @method('PUT')
                <input type="hidden" name="_tab" value="{{ $type }}">

                <div>
                    <p class="text-xs font-mono font-semibold text-[#E26B3D] uppercase tracking-widest mb-4">
                        {{ $types[$type] ?? $type }} — Email Template
                    </p>
                    <p class="text-xs text-slate-500 font-mono mb-4">
                        Available placeholders:
                        <code class="bg-slate-100 px-1.5 py-0.5 rounded text-slate-700">&#123;&#123;name&#125;&#125;</code>
                        <code class="bg-slate-100 px-1.5 py-0.5 rounded text-slate-700">&#123;&#123;position&#125;&#125;</code>
                        <code class="bg-slate-100 px-1.5 py-0.5 rounded text-slate-700">&#123;&#123;company&#125;&#125;</code>
                        <code class="bg-slate-100 px-1.5 py-0.5 rounded text-slate-700">&#123;&#123;department&#125;&#125;</code>
                    </p>
                </div>

                <div>
                    <label class="block text-xs font-mono font-medium text-slate-600 mb-1.5 uppercase tracking-wider">
                        Subject Line <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="subject"
                           value="{{ old('subject', $template->subject) }}"
                           class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#E26B3D] font-mono"
                           required>
                </div>

                <div>
                    <label class="block text-xs font-mono font-medium text-slate-600 mb-1.5 uppercase tracking-wider">
                        Email Body <span class="text-red-500">*</span>
                    </label>
                    <textarea name="body" rows="12"
                              class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#E26B3D] font-mono resize-y"
                              required>{{ old('body', $template->body) }}</textarea>
                    <p class="mt-1 text-xs font-mono text-slate-400">Line breaks are preserved in the sent email.</p>
                </div>

                <div class="flex items-center justify-between pt-2">
                    <p class="text-xs font-mono text-slate-400">
                        Last updated: {{ $template->updated_at->format('M j, Y H:i') }}
                    </p>
                    @if($canEditTemplates)
                    <button type="submit"
                            class="inline-flex items-center gap-2 rounded-lg bg-[#E26B3D] px-5 py-2.5 text-sm font-mono font-medium text-white hover:bg-[#c8602a] transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Save Template
                    </button>
                    @endif
                </div>
            </form>
        </div>
        @endforeach
    </div>

    {{-- Info card --}}
    <div class="bg-white rounded-xl border border-slate-200 p-5">
        <h3 class="text-xs font-mono font-semibold text-[#E26B3D] uppercase tracking-widest mb-3">How it works</h3>
        <ul class="space-y-2 text-sm text-slate-600 font-mono">
            <li class="flex items-start gap-2">
                <svg class="w-4 h-4 text-[#E26B3D] mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"/></svg>
                When a new employee is created, PDF documents (Job Offer, NDA, Contract) are automatically generated and stored in their attachments.
            </li>
            <li class="flex items-start gap-2">
                <svg class="w-4 h-4 text-[#E26B3D] mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"/></svg>
                On the employee profile page, use <strong>Send Job Offer</strong> or <strong>Send Contract</strong> to email the relevant document to the employee.
            </li>
            <li class="flex items-start gap-2">
                <svg class="w-4 h-4 text-[#E26B3D] mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"/></svg>
                Placeholders like <code class="bg-slate-100 px-1 rounded">&#123;&#123;name&#125;&#125;</code> are replaced with the employee's actual data at send time.
            </li>
        </ul>
    </div>

</div>
@endsection

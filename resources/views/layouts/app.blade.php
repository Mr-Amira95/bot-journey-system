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
@php
    $empActive    = request()->routeIs('employees*');
    $cliActive    = request()->routeIs('clients*');
    $projActive   = request()->routeIs('projects*');
    $taskActive   = request()->routeIs('tasks*');
    $convActive   = request()->routeIs('conversations*');
    $callActive   = request()->routeIs('calls*');
$expActive      = request()->routeIs('expense*');
    $deptActive     = request()->routeIs('departments*');
    $roleActive     = request()->routeIs('roles*');
    $emailTplActive = request()->routeIs('email-templates*');
    $dashActive     = request()->routeIs('dashboard');
    $payrollActive  = request()->routeIs('payroll*');
    $salaryActive   = request()->routeIs('salary-histories*');
    $invoiceActive  = request()->routeIs('invoices*');
    $recurActive    = request()->routeIs('recurring-expenses*');
    $budgetActive   = request()->routeIs('project-budgets*');
    $leaveTypeActive   = request()->routeIs('leave-types*');
    $leaveReqActive    = request()->routeIs('leave-requests*');
    $leaveBalActive    = request()->routeIs('leave-balances*');
    $overtimeActive    = request()->routeIs('overtime-requests*');
    $breaksActive      = request()->routeIs('employee-breaks*');
    $scheduleActive    = request()->routeIs('work-schedules*');
    $attendanceActive  = request()->routeIs('attendance*');
    $activityLogActive = request()->routeIs('activity-logs*');
    $peopleActive   = $empActive || $cliActive;
    $workActive     = $projActive || $taskActive;
    $commsActive    = $convActive || $callActive;
    $adminActive    = $deptActive || $roleActive || $emailTplActive || $activityLogActive;
    $financeActive  = $payrollActive || $salaryActive || $invoiceActive || $recurActive || $budgetActive || $expActive;
    $hrActive       = $leaveTypeActive || $leaveReqActive || $leaveBalActive || $overtimeActive || $breaksActive || $scheduleActive || $attendanceActive;

    $user = auth()->user();
    $hasPeopleAccess  = $user->hasPermission('view_employees') || $user->hasPermission('view_clients');
    $hasWorkAccess    = $user->hasPermission('view_projects')   || $user->hasPermission('view_tasks');
    $hasCommsAccess   = $user->hasPermission('view_conversations') || $user->hasPermission('view_calls');
    $hasFinanceAccess = $user->hasPermission('view_expenses')   || $user->hasPermission('view_payroll')
                     || $user->hasPermission('view_salary_histories') || $user->hasPermission('view_invoices')
                     || $user->hasPermission('view_recurring_expenses') || $user->hasPermission('view_project_budgets');
    $hasHrAccess      = $user->hasPermission('view_leave_requests') || $user->hasPermission('view_leave_balances')
                     || $user->hasPermission('view_overtime_requests') || $user->hasPermission('view_employee_breaks')
                     || $user->hasPermission('view_leave_types') || $user->hasPermission('view_work_schedules')
                     || $user->hasPermission('view_attendance');
    $hasAdminAccess   = $user->hasPermission('view_departments') || $user->hasPermission('view_roles')
                     || $user->hasPermission('view_email_templates') || $user->hasPermission('view_activity_logs');
@endphp
<body class="bg-stone-50 antialiased font-sans"
      x-data="{
          sidebarOpen: false,
          sidebarCollapsed: localStorage.getItem('sidebarCollapsed') !== 'false',
          groups: {
              people:  {{ $peopleActive  ? 'true' : 'false' }},
              work:    {{ $workActive    ? 'true' : 'false' }},
              comms:   {{ $commsActive   ? 'true' : 'false' }},
              finance: {{ $financeActive ? 'true' : 'false' }},
              hr:      {{ $hrActive      ? 'true' : 'false' }},
              admin:   {{ $adminActive   ? 'true' : 'false' }},
          },
          toggleGroup(key) {
              const wasOpen = this.groups[key];
              this.groups.people  = false;
              this.groups.work    = false;
              this.groups.comms   = false;
              this.groups.finance = false;
              this.groups.hr      = false;
              this.groups.admin   = false;
              if (!wasOpen) this.groups[key] = true;
          }
      }"
      x-init="$watch('sidebarCollapsed', val => localStorage.setItem('sidebarCollapsed', val))">

    {{-- Mobile backdrop --}}
    <div x-show="sidebarOpen"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="sidebarOpen = false"
         class="fixed inset-0 bg-[#0f1b3d]/80 z-20 lg:hidden"></div>

    {{-- Sidebar --}}
    <aside class="fixed inset-y-0 left-0 z-30 bg-[#0f1b3d] flex flex-col overflow-hidden
                  transition-all duration-200 ease-in-out
                  -translate-x-full lg:translate-x-0 w-64"
           :class="{
               'translate-x-0': sidebarOpen,
               'lg:w-16':       sidebarCollapsed,
           }">

        {{-- Logo --}}
        <div class="flex items-center h-16 border-b border-white/10 shrink-0 overflow-hidden transition-all duration-200"
             :class="sidebarCollapsed ? 'justify-center' : 'px-4'">
            {{-- Icon only — collapsed --}}
            <img src="{{ asset('icon.png') }}" alt="Bot Journey"
                 class="h-9 w-9 object-contain shrink-0"
                 x-show="sidebarCollapsed">
            {{-- Full wordmark — expanded --}}
            <img src="{{ asset('logo.png') }}" alt="Bot Journey"
                 class="h-8 object-contain max-w-full"
                 x-show="!sidebarCollapsed"
                 x-transition:enter="transition-opacity duration-150 delay-75"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition-opacity duration-75"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0">
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 py-3 overflow-y-auto overflow-x-hidden transition-all duration-200"
             :class="sidebarCollapsed ? 'px-2' : 'px-3'">

            @php
                $navLinkBase = 'flex items-center rounded-lg text-sm transition-colors font-mono';
                $navActive   = 'bg-[#E26B3D] text-white';
                $navInactive = 'text-[#F2EEE5]/60 hover:bg-white/10 hover:text-[#F2EEE5]';
            @endphp

            {{-- Dashboard --}}
            <a href="{{ route('dashboard') }}"
               class="{{ $navLinkBase }} {{ $dashActive ? $navActive : $navInactive }}"
               :class="sidebarCollapsed ? 'justify-center py-2.5' : 'gap-3 px-3 py-2.5'"
               :title="sidebarCollapsed ? 'Dashboard' : ''">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                <span class="whitespace-nowrap" x-show="!sidebarCollapsed"
                      x-transition:enter="transition-opacity duration-150 delay-75" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                      x-transition:leave="transition-opacity duration-75" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">Dashboard</span>
            </a>

            {{-- ── People ─────────────────────────────── --}}
            @if($hasPeopleAccess)
            <div class="mt-2">
                <button x-show="!sidebarCollapsed" @click="toggleGroup('people')"
                        class="w-full flex items-center justify-between px-3 py-1 mb-0.5 rounded-lg text-xs font-semibold uppercase tracking-widest transition-colors select-none
                               {{ $peopleActive ? 'text-[#E26B3D]' : 'text-white/30 hover:text-white/50' }}">
                    <span>People</span>
                    <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="groups.people ? 'rotate-90' : ''"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
                <div x-show="sidebarCollapsed || groups.people" class="space-y-0.5">
                    @if(auth()->user()->hasPermission('view_employees'))
                    <a href="{{ route('employees.index') }}"
                       class="{{ $navLinkBase }} {{ $empActive ? $navActive : $navInactive }}"
                       :class="sidebarCollapsed ? 'justify-center py-2.5' : 'gap-3 py-2.5 pl-7 pr-3'"
                       :title="sidebarCollapsed ? 'Employees' : ''">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <span class="whitespace-nowrap" x-show="!sidebarCollapsed"
                              x-transition:enter="transition-opacity duration-150 delay-75" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                              x-transition:leave="transition-opacity duration-75" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">Employees</span>
                    </a>
                    @endif
                    @if(auth()->user()->hasPermission('view_clients'))
                    <a href="{{ route('clients.index') }}"
                       class="{{ $navLinkBase }} {{ $cliActive ? $navActive : $navInactive }}"
                       :class="sidebarCollapsed ? 'justify-center py-2.5' : 'gap-3 py-2.5 pl-7 pr-3'"
                       :title="sidebarCollapsed ? 'Clients' : ''">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        <span class="whitespace-nowrap" x-show="!sidebarCollapsed"
                              x-transition:enter="transition-opacity duration-150 delay-75" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                              x-transition:leave="transition-opacity duration-75" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">Clients</span>
                    </a>
                    @endif
                </div>
            </div>
            @endif

            {{-- ── Work ───────────────────────────────── --}}
            @if($hasWorkAccess)
            <div class="mt-2">
                <button x-show="!sidebarCollapsed" @click="toggleGroup('work')"
                        class="w-full flex items-center justify-between px-3 py-1 mb-0.5 rounded-lg text-xs font-semibold uppercase tracking-widest transition-colors select-none
                               {{ $workActive ? 'text-[#E26B3D]' : 'text-white/30 hover:text-white/50' }}">
                    <span>Work</span>
                    <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="groups.work ? 'rotate-90' : ''"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
                <div x-show="sidebarCollapsed || groups.work" class="space-y-0.5">
                    @if(auth()->user()->hasPermission('view_projects'))
                    <a href="{{ route('projects.index') }}"
                       class="{{ $navLinkBase }} {{ $projActive ? $navActive : $navInactive }}"
                       :class="sidebarCollapsed ? 'justify-center py-2.5' : 'gap-3 py-2.5 pl-7 pr-3'"
                       :title="sidebarCollapsed ? 'Projects' : ''">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                        </svg>
                        <span class="whitespace-nowrap" x-show="!sidebarCollapsed"
                              x-transition:enter="transition-opacity duration-150 delay-75" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                              x-transition:leave="transition-opacity duration-75" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">Projects</span>
                    </a>
                    @endif
                    @if(auth()->user()->hasPermission('view_tasks'))
                    <a href="{{ route('tasks.index') }}"
                       class="{{ $navLinkBase }} {{ $taskActive ? $navActive : $navInactive }}"
                       :class="sidebarCollapsed ? 'justify-center py-2.5' : 'gap-3 py-2.5 pl-7 pr-3'"
                       :title="sidebarCollapsed ? 'Tasks' : ''">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                        <span class="whitespace-nowrap" x-show="!sidebarCollapsed"
                              x-transition:enter="transition-opacity duration-150 delay-75" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                              x-transition:leave="transition-opacity duration-75" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">Tasks</span>
                    </a>
                    @endif
                </div>
            </div>
            @endif

            {{-- ── Communication ───────────────────────── --}}
            @if($hasCommsAccess)
            <div class="mt-2">
                <button x-show="!sidebarCollapsed" @click="toggleGroup('comms')"
                        class="w-full flex items-center justify-between px-3 py-1 mb-0.5 rounded-lg text-xs font-semibold uppercase tracking-widest transition-colors select-none
                               {{ $commsActive ? 'text-[#E26B3D]' : 'text-white/30 hover:text-white/50' }}">
                    <span>Communication</span>
                    <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="groups.comms ? 'rotate-90' : ''"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
                <div x-show="sidebarCollapsed || groups.comms" class="space-y-0.5">
                    @if(auth()->user()->hasPermission('view_conversations'))
                    <a href="{{ route('conversations.index') }}"
                       class="{{ $navLinkBase }} {{ $convActive ? $navActive : $navInactive }}"
                       :class="sidebarCollapsed ? 'justify-center py-2.5' : 'gap-3 py-2.5 pl-7 pr-3'"
                       :title="sidebarCollapsed ? 'Messages' : ''">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                        <span class="whitespace-nowrap" x-show="!sidebarCollapsed"
                              x-transition:enter="transition-opacity duration-150 delay-75" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                              x-transition:leave="transition-opacity duration-75" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">Messages</span>
                    </a>
                    @endif
                    @if(auth()->user()->hasPermission('view_calls'))
                    <a href="{{ route('calls.index') }}"
                       class="{{ $navLinkBase }} {{ $callActive ? $navActive : $navInactive }}"
                       :class="sidebarCollapsed ? 'justify-center py-2.5' : 'gap-3 py-2.5 pl-7 pr-3'"
                       :title="sidebarCollapsed ? 'Calls' : ''">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                        <span class="whitespace-nowrap" x-show="!sidebarCollapsed"
                              x-transition:enter="transition-opacity duration-150 delay-75" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                              x-transition:leave="transition-opacity duration-75" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">Calls</span>
                    </a>
                    @endif
                </div>
            </div>
            @endif

            {{-- ── Finance ─────────────────────────────── --}}
            @if($hasFinanceAccess)
            <div class="mt-2">
                <button x-show="!sidebarCollapsed" @click="toggleGroup('finance')"
                        class="w-full flex items-center justify-between px-3 py-1 mb-0.5 rounded-lg text-xs font-semibold uppercase tracking-widest transition-colors select-none
                               {{ $financeActive ? 'text-[#E26B3D]' : 'text-white/30 hover:text-white/50' }}">
                    <span>Finance</span>
                    <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="groups.finance ? 'rotate-90' : ''"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
                <div x-show="sidebarCollapsed || groups.finance" class="space-y-0.5">
                    @if(auth()->user()->hasPermission('view_expenses'))
                    <a href="{{ route('expenses.index') }}"
                       class="{{ $navLinkBase }} {{ $expActive ? $navActive : $navInactive }}"
                       :class="sidebarCollapsed ? 'justify-center py-2.5' : 'gap-3 py-2.5 pl-7 pr-3'"
                       :title="sidebarCollapsed ? 'Expenses' : ''">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <span class="whitespace-nowrap" x-show="!sidebarCollapsed"
                              x-transition:enter="transition-opacity duration-150 delay-75" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                              x-transition:leave="transition-opacity duration-75" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">Expenses</span>
                    </a>
                    @endif
                    @if(auth()->user()->hasPermission('view_payroll'))
                    <a href="{{ route('payroll.index') }}"
                       class="{{ $navLinkBase }} {{ $payrollActive ? $navActive : $navInactive }}"
                       :class="sidebarCollapsed ? 'justify-center py-2.5' : 'gap-3 py-2.5 pl-7 pr-3'"
                       :title="sidebarCollapsed ? 'Payroll' : ''">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                        </svg>
                        <span class="whitespace-nowrap" x-show="!sidebarCollapsed"
                              x-transition:enter="transition-opacity duration-150 delay-75" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                              x-transition:leave="transition-opacity duration-75" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">Payroll</span>
                    </a>
                    @endif
                    @if(auth()->user()->hasPermission('view_salary_histories'))
                    <a href="{{ route('salary-histories.index') }}"
                       class="{{ $navLinkBase }} {{ $salaryActive ? $navActive : $navInactive }}"
                       :class="sidebarCollapsed ? 'justify-center py-2.5' : 'gap-3 py-2.5 pl-7 pr-3'"
                       :title="sidebarCollapsed ? 'Salary History' : ''">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="whitespace-nowrap" x-show="!sidebarCollapsed"
                              x-transition:enter="transition-opacity duration-150 delay-75" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                              x-transition:leave="transition-opacity duration-75" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">Salary History</span>
                    </a>
                    @endif
                    @if(auth()->user()->hasPermission('view_invoices'))
                    <a href="{{ route('invoices.index') }}"
                       class="{{ $navLinkBase }} {{ $invoiceActive ? $navActive : $navInactive }}"
                       :class="sidebarCollapsed ? 'justify-center py-2.5' : 'gap-3 py-2.5 pl-7 pr-3'"
                       :title="sidebarCollapsed ? 'Invoices' : ''">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span class="whitespace-nowrap" x-show="!sidebarCollapsed"
                              x-transition:enter="transition-opacity duration-150 delay-75" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                              x-transition:leave="transition-opacity duration-75" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">Invoices</span>
                    </a>
                    @endif
                    @if(auth()->user()->hasPermission('view_recurring_expenses'))
                    <a href="{{ route('recurring-expenses.index') }}"
                       class="{{ $navLinkBase }} {{ $recurActive ? $navActive : $navInactive }}"
                       :class="sidebarCollapsed ? 'justify-center py-2.5' : 'gap-3 py-2.5 pl-7 pr-3'"
                       :title="sidebarCollapsed ? 'Recurring Expenses' : ''">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        <span class="whitespace-nowrap" x-show="!sidebarCollapsed"
                              x-transition:enter="transition-opacity duration-150 delay-75" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                              x-transition:leave="transition-opacity duration-75" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">Recurring Expenses</span>
                    </a>
                    @endif
                    @if(auth()->user()->hasPermission('view_project_budgets'))
                    <a href="{{ route('project-budgets.index') }}"
                       class="{{ $navLinkBase }} {{ $budgetActive ? $navActive : $navInactive }}"
                       :class="sidebarCollapsed ? 'justify-center py-2.5' : 'gap-3 py-2.5 pl-7 pr-3'"
                       :title="sidebarCollapsed ? 'Project Budgets' : ''">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/>
                        </svg>
                        <span class="whitespace-nowrap" x-show="!sidebarCollapsed"
                              x-transition:enter="transition-opacity duration-150 delay-75" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                              x-transition:leave="transition-opacity duration-75" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">Project Budgets</span>
                    </a>
                    @endif
                </div>
            </div>
            @endif

            {{-- ── HR ─────────────────────────────────── --}}
            @if($hasHrAccess)
            <div class="mt-2">
                <button x-show="!sidebarCollapsed" @click="toggleGroup('hr')"
                        class="w-full flex items-center justify-between px-3 py-1 mb-0.5 rounded-lg text-xs font-semibold uppercase tracking-widest transition-colors select-none
                               {{ $hrActive ? 'text-[#E26B3D]' : 'text-white/30 hover:text-white/50' }}">
                    <span>HR</span>
                    <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="groups.hr ? 'rotate-90' : ''"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
                <div x-show="sidebarCollapsed || groups.hr" class="space-y-0.5">
                    @if(auth()->user()->hasPermission('view_leave_requests'))
                    <a href="{{ route('leave-requests.index') }}"
                       class="{{ $navLinkBase }} {{ $leaveReqActive ? $navActive : $navInactive }}"
                       :class="sidebarCollapsed ? 'justify-center py-2.5' : 'gap-3 py-2.5 pl-7 pr-3'"
                       :title="sidebarCollapsed ? 'Leave Requests' : ''">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span class="whitespace-nowrap" x-show="!sidebarCollapsed"
                              x-transition:enter="transition-opacity duration-150 delay-75" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                              x-transition:leave="transition-opacity duration-75" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">Leave Requests</span>
                    </a>
                    @endif
                    @if(auth()->user()->hasPermission('view_leave_balances'))
                    <a href="{{ route('leave-balances.index') }}"
                       class="{{ $navLinkBase }} {{ $leaveBalActive ? $navActive : $navInactive }}"
                       :class="sidebarCollapsed ? 'justify-center py-2.5' : 'gap-3 py-2.5 pl-7 pr-3'"
                       :title="sidebarCollapsed ? 'Leave Balances' : ''">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        <span class="whitespace-nowrap" x-show="!sidebarCollapsed"
                              x-transition:enter="transition-opacity duration-150 delay-75" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                              x-transition:leave="transition-opacity duration-75" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">Leave Balances</span>
                    </a>
                    @endif
                    @if(auth()->user()->hasPermission('view_overtime_requests'))
                    <a href="{{ route('overtime-requests.index') }}"
                       class="{{ $navLinkBase }} {{ $overtimeActive ? $navActive : $navInactive }}"
                       :class="sidebarCollapsed ? 'justify-center py-2.5' : 'gap-3 py-2.5 pl-7 pr-3'"
                       :title="sidebarCollapsed ? 'Overtime' : ''">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="whitespace-nowrap" x-show="!sidebarCollapsed"
                              x-transition:enter="transition-opacity duration-150 delay-75" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                              x-transition:leave="transition-opacity duration-75" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">Overtime</span>
                    </a>
                    @endif
                    @if(auth()->user()->hasPermission('view_employee_breaks'))
                    <a href="{{ route('employee-breaks.index') }}"
                       class="{{ $navLinkBase }} {{ $breaksActive ? $navActive : $navInactive }}"
                       :class="sidebarCollapsed ? 'justify-center py-2.5' : 'gap-3 py-2.5 pl-7 pr-3'"
                       :title="sidebarCollapsed ? 'Breaks' : ''">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                        </svg>
                        <span class="whitespace-nowrap" x-show="!sidebarCollapsed"
                              x-transition:enter="transition-opacity duration-150 delay-75" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                              x-transition:leave="transition-opacity duration-75" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">Breaks</span>
                    </a>
                    @endif
                    @if(auth()->user()->hasPermission('view_leave_types'))
                    <a href="{{ route('leave-types.index') }}"
                       class="{{ $navLinkBase }} {{ $leaveTypeActive ? $navActive : $navInactive }}"
                       :class="sidebarCollapsed ? 'justify-center py-2.5' : 'gap-3 py-2.5 pl-7 pr-3'"
                       :title="sidebarCollapsed ? 'Leave Types' : ''">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                        <span class="whitespace-nowrap" x-show="!sidebarCollapsed"
                              x-transition:enter="transition-opacity duration-150 delay-75" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                              x-transition:leave="transition-opacity duration-75" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">Leave Types</span>
                    </a>
                    @endif
                    @if(auth()->user()->hasPermission('view_work_schedules'))
                    <a href="{{ route('work-schedules.index') }}"
                       class="{{ $navLinkBase }} {{ $scheduleActive ? $navActive : $navInactive }}"
                       :class="sidebarCollapsed ? 'justify-center py-2.5' : 'gap-3 py-2.5 pl-7 pr-3'"
                       :title="sidebarCollapsed ? 'Work Schedules' : ''">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        <span class="whitespace-nowrap" x-show="!sidebarCollapsed"
                              x-transition:enter="transition-opacity duration-150 delay-75" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                              x-transition:leave="transition-opacity duration-75" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">Work Schedules</span>
                    </a>
                    @endif
                    @if(auth()->user()->hasPermission('view_attendance'))
                    <a href="{{ route('attendance.index') }}"
                       class="{{ $navLinkBase }} {{ $attendanceActive ? $navActive : $navInactive }}"
                       :class="sidebarCollapsed ? 'justify-center py-2.5' : 'gap-3 py-2.5 pl-7 pr-3'"
                       :title="sidebarCollapsed ? 'Attendance' : ''">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                        <span class="whitespace-nowrap" x-show="!sidebarCollapsed"
                              x-transition:enter="transition-opacity duration-150 delay-75" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                              x-transition:leave="transition-opacity duration-75" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">Attendance</span>
                    </a>
                    @endif
                </div>
            </div>
            @endif

            {{-- ── Administration ──────────────────────── --}}
            @if($hasAdminAccess)
            <div class="mt-2">
                <button x-show="!sidebarCollapsed" @click="toggleGroup('admin')"
                        class="w-full flex items-center justify-between px-3 py-1 mb-0.5 rounded-lg text-xs font-semibold uppercase tracking-widest transition-colors select-none
                               {{ $adminActive ? 'text-[#E26B3D]' : 'text-white/30 hover:text-white/50' }}">
                    <span>Administration</span>
                    <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="groups.admin ? 'rotate-90' : ''"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
                <div x-show="sidebarCollapsed || groups.admin" class="space-y-0.5">
                    @if(auth()->user()->hasPermission('view_departments'))
                    <a href="{{ route('departments.index') }}"
                       class="{{ $navLinkBase }} {{ $deptActive ? $navActive : $navInactive }}"
                       :class="sidebarCollapsed ? 'justify-center py-2.5' : 'gap-3 py-2.5 pl-7 pr-3'"
                       :title="sidebarCollapsed ? 'Departments' : ''">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        <span class="whitespace-nowrap" x-show="!sidebarCollapsed"
                              x-transition:enter="transition-opacity duration-150 delay-75" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                              x-transition:leave="transition-opacity duration-75" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">Departments</span>
                    </a>
                    @endif
                    @if(auth()->user()->hasPermission('view_roles'))
                    <a href="{{ route('roles.index') }}"
                       class="{{ $navLinkBase }} {{ $roleActive ? $navActive : $navInactive }}"
                       :class="sidebarCollapsed ? 'justify-center py-2.5' : 'gap-3 py-2.5 pl-7 pr-3'"
                       :title="sidebarCollapsed ? 'Roles & Permissions' : ''">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        <span class="whitespace-nowrap" x-show="!sidebarCollapsed"
                              x-transition:enter="transition-opacity duration-150 delay-75" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                              x-transition:leave="transition-opacity duration-75" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">Roles & Permissions</span>
                    </a>
                    @endif
                    @if(auth()->user()->hasPermission('view_email_templates'))
                    <a href="{{ route('email-templates.index') }}"
                       class="{{ $navLinkBase }} {{ $emailTplActive ? $navActive : $navInactive }}"
                       :class="sidebarCollapsed ? 'justify-center py-2.5' : 'gap-3 py-2.5 pl-7 pr-3'"
                       :title="sidebarCollapsed ? 'Email Templates' : ''">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        <span class="whitespace-nowrap" x-show="!sidebarCollapsed"
                              x-transition:enter="transition-opacity duration-150 delay-75" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                              x-transition:leave="transition-opacity duration-75" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">Email Templates</span>
                    </a>
                    @endif
                    @if(auth()->user()->hasPermission('view_activity_logs'))
                    <a href="{{ route('activity-logs.index') }}"
                       class="{{ $navLinkBase }} {{ $activityLogActive ? $navActive : $navInactive }}"
                       :class="sidebarCollapsed ? 'justify-center py-2.5' : 'gap-3 py-2.5 pl-7 pr-3'"
                       :title="sidebarCollapsed ? 'Activity Logs' : ''">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                        </svg>
                        <span class="whitespace-nowrap" x-show="!sidebarCollapsed"
                              x-transition:enter="transition-opacity duration-150 delay-75" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                              x-transition:leave="transition-opacity duration-75" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">Activity Logs</span>
                    </a>
                    @endif
                </div>
            </div>
            @endif

        </nav>

        {{-- User section --}}
        <div class="py-4 border-t border-white/10 shrink-0 transition-all duration-200"
             :class="sidebarCollapsed ? 'px-2' : 'px-3'">
            <div class="flex items-center py-2 rounded-lg transition-all duration-200"
                 :class="sidebarCollapsed ? 'justify-center' : 'gap-3 px-3'">
                <div class="w-8 h-8 rounded-full bg-[#E26B3D] flex items-center justify-center text-white text-sm font-semibold shrink-0"
                     :title="sidebarCollapsed ? '{{ auth()->user()->name }}' : ''">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0"
                     x-show="!sidebarCollapsed"
                     x-transition:enter="transition-opacity duration-150 delay-75"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="transition-opacity duration-75"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0">
                    <p class="text-sm font-medium text-[#F2EEE5] truncate">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-[#F2EEE5]/50 truncate font-mono">{{ auth()->user()->email }}</p>
                </div>
            </div>

            {{-- Sign out — full button when expanded --}}
            <form method="POST" action="{{ route('logout') }}" class="mt-1"
                  x-show="!sidebarCollapsed"
                  x-transition:enter="transition-opacity duration-150 delay-75"
                  x-transition:enter-start="opacity-0"
                  x-transition:enter-end="opacity-100"
                  x-transition:leave="transition-opacity duration-75"
                  x-transition:leave-start="opacity-100"
                  x-transition:leave-end="opacity-0">
                @csrf
                <button type="submit"
                        class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-[#F2EEE5]/60 hover:bg-white/10 hover:text-[#F2EEE5] transition-colors font-mono">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    <span>Sign out</span>
                </button>
            </form>

            {{-- Sign out — icon only when collapsed --}}
            <form method="POST" action="{{ route('logout') }}" class="mt-1 flex justify-center"
                  x-show="sidebarCollapsed">
                @csrf
                <button type="submit"
                        class="p-2 rounded-lg text-[#F2EEE5]/60 hover:bg-white/10 hover:text-[#F2EEE5] transition-colors"
                        title="Sign out">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                </button>
            </form>
        </div>
    </aside>

    {{-- Top header --}}
    <header class="fixed top-0 right-0 left-0 h-16 bg-white border-b border-slate-200 z-10 flex items-center px-4 lg:px-6 gap-4 transition-all duration-200 ease-in-out"
            :class="{ 'lg:left-64': !sidebarCollapsed, 'lg:left-16': sidebarCollapsed }">
        {{-- Mobile menu toggle --}}
        <button @click="sidebarOpen = !sidebarOpen"
                class="lg:hidden p-2 rounded-lg text-slate-500 hover:bg-stone-100 hover:text-slate-700 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>

        {{-- Desktop sidebar toggle --}}
        <button @click="sidebarCollapsed = !sidebarCollapsed"
                class="max-lg:hidden flex p-2 rounded-lg text-slate-500 hover:bg-stone-100 hover:text-slate-700 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>

        <h1 class="text-lg font-semibold text-slate-800 flex-1">@yield('page-title', 'Dashboard')</h1>

        <div class="flex items-center gap-3">

            {{-- Whiteboard icon --}}
            @if(auth()->user()->hasPermission('view_whiteboards'))
            <a href="{{ route('whiteboards.index') }}"
               title="Whiteboards"
               class="relative w-9 h-9 flex items-center justify-center rounded-full hover:bg-slate-100 transition-colors {{ request()->routeIs('whiteboards.*') ? 'text-[#E26B3D]' : 'text-slate-500 hover:text-slate-700' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                    <rect x="2" y="3" width="20" height="14" rx="2" stroke="currentColor" stroke-width="1.75" fill="none"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 21h8M12 17v4"/>
                </svg>
            </a>
            @endif

            {{-- Notification bell --}}
            @if(false)
            <div x-data="{
                    open: false,
                    notifications: [],
                    unreadCount: 0,

                    init() {
                        fetch('/notifications', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                            .then(r => r.json())
                            .then(d => { this.notifications = d.notifications; this.unreadCount = d.unread_count; });
                    },

                    markAllRead() {
                        fetch('/notifications/read-all', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                'X-Requested-With': 'XMLHttpRequest',
                            }
                        }).then(() => {
                            this.notifications = this.notifications.map(n => ({ ...n, read_at: 'read' }));
                            this.unreadCount = 0;
                        });
                    },

                    markRead(id) {
                        const n = this.notifications.find(n => n.id === id);
                        if (n && !n.read_at) {
                            fetch('/notifications/' + id + '/read', {
                                method: 'POST',
                                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }
                            });
                            n.read_at = 'read';
                            this.unreadCount = Math.max(0, this.unreadCount - 1);
                        }
                        if (n?.data?.url) window.location.href = n.data.url;
                    },

                    iconColor(type) {
                        const map = {
                            task_assigned: 'bg-blue-500',
                            project_assigned: 'bg-purple-500',
                            leave_status: 'bg-green-500',
                            overtime_status: 'bg-orange-500',
                            expense_status: 'bg-emerald-500',
                            whiteboard_shared: 'bg-indigo-500',
                        };
                        return map[type] ?? 'bg-slate-400';
                    },

                    iconLetter(type) {
                        const map = {
                            task_assigned: 'T',
                            project_assigned: 'P',
                            leave_status: 'L',
                            overtime_status: 'O',
                            expense_status: 'E',
                            whiteboard_shared: 'W',
                        };
                        return map[type] ?? 'N';
                    },
                }"
                 @new-notification.window="
                     notifications.unshift({ id: $event.detail.id ?? '', data: $event.detail, read_at: null, created_at: 'just now' });
                     unreadCount++;
                 "
                 @click.outside="open = false"
                 class="relative">

                <button @click="open = !open"
                        class="relative p-2 rounded-lg text-slate-500 hover:bg-stone-100 hover:text-slate-700 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    <span x-show="unreadCount > 0"
                          x-text="unreadCount > 9 ? '9+' : unreadCount"
                          class="absolute -top-0.5 -right-0.5 min-w-[1.1rem] h-[1.1rem] px-0.5 flex items-center justify-center rounded-full bg-[#E26B3D] text-white text-[10px] font-bold leading-none"
                          style="display:none;"></span>
                </button>

                <div x-show="open"
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                     x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-100"
                     x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                     x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                     class="absolute right-0 mt-2 w-80 bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden z-50"
                     style="display:none; top: calc(100% + 4px);">

                    <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100">
                        <span class="text-sm font-semibold text-slate-800">Notifications</span>
                        <button x-show="unreadCount > 0"
                                @click.stop="markAllRead()"
                                class="text-xs text-[#E26B3D] hover:underline font-medium"
                                style="display:none;">Mark all read</button>
                    </div>

                    <ul class="max-h-80 overflow-y-auto divide-y divide-slate-100">
                        <template x-if="notifications.length === 0">
                            <li class="px-4 py-6 text-center text-sm text-slate-400">No notifications yet</li>
                        </template>
                        <template x-for="n in notifications" :key="n.id">
                            <li @click="markRead(n.id)"
                                class="flex items-start gap-3 px-4 py-3 cursor-pointer hover:bg-stone-50 transition-colors"
                                :class="{ 'bg-blue-50/40': !n.read_at }">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold shrink-0 mt-0.5"
                                     :class="iconColor(n.data?.type)">
                                    <span x-text="iconLetter(n.data?.type)"></span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-slate-800 leading-snug" x-text="n.data?.title"></p>
                                    <p class="text-xs text-slate-500 mt-0.5 leading-snug line-clamp-2" x-text="n.data?.body"></p>
                                    <p class="text-xs text-slate-400 mt-1" x-text="n.created_at"></p>
                                </div>
                                <div x-show="!n.read_at" class="w-2 h-2 rounded-full bg-[#E26B3D] shrink-0 mt-2" style="display:none;"></div>
                            </li>
                        </template>
                    </ul>
                </div>
            </div>
            @endif

            @yield('header-actions')
        </div>
    </header>

    {{-- Main content --}}
    <main class="pt-16 min-h-screen transition-all duration-200 ease-in-out"
          :class="{ 'lg:ml-64': !sidebarCollapsed, 'lg:ml-16': sidebarCollapsed }">
        <div class="p-6">
            @include('components.flash-messages')
            @yield('content')
        </div>
    </main>


    {{-- ── Global Delete Confirmation Modal ──────────────────── --}}
    <div x-data="{
             isOpen: false,
             action: '',
             open(data) { this.action = data.action; this.isOpen = true; },
             close() { this.isOpen = false; }
         }"
         @confirm:delete.window="open($event.detail)">

        {{-- Backdrop --}}
        <div x-show="isOpen"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             @click="close()"
             class="fixed inset-0 bg-[#0f1b3d]/50 backdrop-blur-sm z-[60]" style="display:none;"></div>

        {{-- Dialog --}}
        <div x-show="isOpen"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
             class="fixed inset-0 z-[61] flex items-center justify-center p-4" style="display:none;">
            <div class="bg-white rounded-2xl shadow-2xl max-w-sm w-full p-6 relative">

                <button @click="close()"
                        class="absolute top-4 right-4 p-1.5 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-stone-100 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>

                <div class="flex items-start gap-4 mb-6">
                    <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </div>
                    <div class="pr-6">
                        <h3 class="text-base font-semibold text-slate-800">Confirm Deletion</h3>
                        <p class="text-sm text-slate-500 mt-1 leading-relaxed">Are you sure you want to delete this? This action cannot be undone.</p>
                    </div>
                </div>

                <div class="flex gap-3">
                    <button type="button" @click="close()"
                            class="flex-1 px-4 py-2.5 text-sm font-mono font-medium text-slate-700 border border-slate-300 rounded-lg bg-white hover:bg-stone-50 transition-colors">
                        Cancel
                    </button>
                    <form :action="action" method="POST" class="flex-1">
                        @csrf
                        <input type="hidden" name="_method" value="DELETE">
                        <button type="submit"
                                class="w-full px-4 py-2.5 text-sm font-mono font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors">
                            Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Global incoming call notification ─────────────────── --}}
    <div x-data="{
             show: false,
             callId: null,
             callType: 'audio',
             callerName: '',
             conversationId: null,
             _audioCtx: null,
             _ringtoneInterval: null,

             startRingtone() {
                 const AudioCtx = window.AudioContext || window.webkitAudioContext;
                 if (!AudioCtx) return;
                 try {
                     const ctx = new AudioCtx();
                     this._audioCtx = ctx;
                     const self = this;
                     const ring = function() {
                         if (!self._audioCtx || self._audioCtx.state === 'closed') return;
                         const osc = ctx.createOscillator();
                         const gain = ctx.createGain();
                         osc.connect(gain);
                         gain.connect(ctx.destination);
                         osc.type = 'sine';
                         osc.frequency.setValueAtTime(880, ctx.currentTime);
                         gain.gain.setValueAtTime(0.3, ctx.currentTime);
                         gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.5);
                         osc.start(ctx.currentTime);
                         osc.stop(ctx.currentTime + 0.5);
                     };
                     ring();
                     this._ringtoneInterval = setInterval(ring, 2000);
                 } catch(e) {}
             },

             stopRingtone() {
                 clearInterval(this._ringtoneInterval);
                 this._ringtoneInterval = null;
                 if (this._audioCtx) { try { this._audioCtx.close(); } catch(e) {} this._audioCtx = null; }
             },

             accept() {
                 this.stopRingtone();
                 const form = document.createElement('form');
                 form.method = 'POST';
                 form.action = '/calls/' + this.callId + '/join';
                 const csrf = document.createElement('input');
                 csrf.type = 'hidden';
                 csrf.name = '_token';
                 csrf.value = document.querySelector('meta[name=csrf-token]').content;
                 form.appendChild(csrf);
                 document.body.appendChild(form);
                 form.submit();
             },
             reject() {
                 this.stopRingtone();
                 fetch('/calls/' + this.callId + '/reject', {
                     method: 'POST',
                     headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }
                 });
                 this.show = false;
             }
         }"
         @incoming-call.window="show = true; callId = $event.detail.call_id; callType = $event.detail.type; callerName = $event.detail.caller_name; startRingtone();"
         x-show="show"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-4"
         class="fixed bottom-6 right-6 z-50 w-80 bg-white rounded-2xl shadow-2xl border border-slate-200 p-4">

        <div class="flex items-start gap-3">
            <div class="w-10 h-10 rounded-full bg-[#E26B3D] flex items-center justify-center text-white font-bold shrink-0"
                 x-text="callerName[0]?.toUpperCase() ?? '?'"></div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-slate-800" x-text="callerName"></p>
                <p class="text-xs text-slate-500 mt-0.5 flex items-center gap-1">
                    <template x-if="callType === 'video'">
                        <svg class="w-3.5 h-3.5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.069A1 1 0 0121 8.87v6.26a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                    </template>
                    <template x-if="callType === 'audio'">
                        <svg class="w-3.5 h-3.5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                    </template>
                    <span x-text="callType === 'video' ? 'Incoming video call' : 'Incoming audio call'"></span>
                </p>
            </div>
        </div>

        <div class="flex gap-2 mt-3">
            <button @click="reject()"
                    class="flex-1 py-2 rounded-xl bg-red-50 text-red-600 hover:bg-red-100 text-sm font-medium transition-colors">
                Decline
            </button>
            <button @click="accept()"
                    class="flex-1 py-2 rounded-xl bg-emerald-500 text-white hover:bg-emerald-600 text-sm font-medium transition-colors">
                Accept
            </button>
        </div>
    </div>

    {{-- Pusher CDN for call notifications (loaded once globally) --}}
    <script src="https://js.pusher.com/8.4.0/pusher.min.js"></script>
    <script>
        (function () {
            const key = @json(config('broadcasting.connections.pusher.key'));
            const cluster = @json(config('broadcasting.connections.pusher.options.cluster'));
            if (!key) return;

            const pusher = new Pusher(key, {
                cluster: cluster,
                authEndpoint: '/broadcasting/auth',
                auth: { headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content } },
            });

            const ch = pusher.subscribe('private-user.{{ auth()->id() }}');
            ch.bind('call.initiated', function (data) {
                window.dispatchEvent(new CustomEvent('incoming-call', { detail: data }));
            });

            const notifCh = pusher.subscribe('private-App.Models.User.{{ auth()->id() }}');
            notifCh.bind_global(function (eventName, data) {
                if (data && data.type) {
                    window.dispatchEvent(new CustomEvent('new-notification', { detail: data }));
                }
            });
        })();
    </script>

</body>
</html>

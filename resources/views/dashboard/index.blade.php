@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')

{{-- ── KPI Cards Row ──────────────────────────────────────────────────────── --}}
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">

    {{-- Employees --}}
    <div class="bg-white rounded-xl border border-slate-200 p-4 flex flex-col gap-1">
        <div class="w-9 h-9 rounded-lg bg-[#E26B3D]/15 flex items-center justify-center mb-1">
            <svg class="w-5 h-5 text-[#E26B3D]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
        </div>
        <p class="text-2xl font-bold text-slate-800">{{ $employeeCount }}</p>
        <p class="text-xs text-slate-500 font-mono">Employees</p>
    </div>

    {{-- Open Projects --}}
    <div class="bg-white rounded-xl border border-slate-200 p-4 flex flex-col gap-1">
        <div class="w-9 h-9 rounded-lg bg-blue-100 flex items-center justify-center mb-1">
            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
        </div>
        <p class="text-2xl font-bold text-slate-800">{{ $openProjectsCount }}</p>
        <p class="text-xs text-slate-500 font-mono">Open Projects</p>
    </div>

    {{-- Overdue Tasks --}}
    <div class="bg-white rounded-xl border border-slate-200 p-4 flex flex-col gap-1">
        <div class="w-9 h-9 rounded-lg bg-red-100 flex items-center justify-center mb-1">
            <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <p class="text-2xl font-bold text-slate-800">{{ $overdueTasksCount }}</p>
        <p class="text-xs text-slate-500 font-mono">Overdue Tasks</p>
    </div>

    {{-- Pending Leaves --}}
    <div class="bg-white rounded-xl border border-slate-200 p-4 flex flex-col gap-1">
        <div class="w-9 h-9 rounded-lg bg-amber-100 flex items-center justify-center mb-1">
            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        </div>
        <p class="text-2xl font-bold text-slate-800">{{ $pendingLeaveCount }}</p>
        <p class="text-xs text-slate-500 font-mono">Pending Leaves</p>
    </div>

    {{-- Monthly Expenses --}}
    <div class="bg-white rounded-xl border border-slate-200 p-4 flex flex-col gap-1">
        <div class="w-9 h-9 rounded-lg bg-purple-100 flex items-center justify-center mb-1">
            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
        </div>
        <p class="text-2xl font-bold text-slate-800">{{ number_format($thisMonthExpenses, 0) }}</p>
        <p class="text-xs text-slate-500 font-mono">This Month's Expenses</p>
    </div>

    {{-- Unpaid Invoices --}}
    <div class="bg-white rounded-xl border border-slate-200 p-4 flex flex-col gap-1">
        <div class="w-9 h-9 rounded-lg bg-emerald-100 flex items-center justify-center mb-1">
            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        </div>
        <p class="text-2xl font-bold text-slate-800">{{ $unpaidInvoicesCount }}</p>
        <p class="text-xs text-slate-500 font-mono">Unpaid Invoices</p>
    </div>

</div>

{{-- ── Charts Row 1: Projects & Tasks ────────────────────────────────────── --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">

    {{-- Project Status Donut --}}
    <div class="bg-white rounded-xl border border-slate-200 p-5">
        <h3 class="text-sm font-semibold text-slate-700 mb-4">Project Status</h3>
        <div class="flex items-center justify-center" style="height:220px;">
            <canvas id="projectStatusChart"></canvas>
        </div>
    </div>

    {{-- Task Status Bar --}}
    <div class="bg-white rounded-xl border border-slate-200 p-5">
        <h3 class="text-sm font-semibold text-slate-700 mb-4">Task Status</h3>
        <div style="height:220px;">
            <canvas id="taskStatusChart"></canvas>
        </div>
    </div>

</div>

{{-- ── Charts Row 2: Finance ──────────────────────────────────────────────── --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">

    {{-- Monthly Expenses --}}
    <div class="bg-white rounded-xl border border-slate-200 p-5">
        <h3 class="text-sm font-semibold text-slate-700 mb-4">Monthly Expenses (last 6 months)</h3>
        <div style="height:220px;">
            <canvas id="expensesChart"></canvas>
        </div>
    </div>

    {{-- Monthly Revenue vs Expenses --}}
    <div class="bg-white rounded-xl border border-slate-200 p-5">
        <h3 class="text-sm font-semibold text-slate-700 mb-4">Revenue vs Expenses (last 6 months)</h3>
        <div style="height:220px;">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>

</div>

{{-- ── Data Tables Row ─────────────────────────────────────────────────────── --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

    {{-- Overdue Tasks --}}
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="flex items-center justify-between px-5 py-3 border-b border-slate-100">
            <h3 class="text-sm font-semibold text-slate-700">Overdue Tasks</h3>
            <a href="{{ route('tasks.index') }}" class="text-xs text-[#E26B3D] hover:underline">View all</a>
        </div>
        @if($overdueTasks->isEmpty())
            <p class="px-5 py-8 text-sm text-slate-400 text-center">No overdue tasks</p>
        @else
            <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr class="text-xs text-slate-400 uppercase border-b border-slate-100">
                    <th class="px-5 py-2 text-left font-medium">Task</th>
                    <th class="px-3 py-2 text-left font-medium">Project</th>
                    <th class="px-3 py-2 text-left font-medium">Due</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-50">
                @foreach($overdueTasks as $task)
                    <tr class="hover:bg-stone-50 transition-colors">
                        <td class="px-5 py-2.5 font-medium text-slate-800 max-w-[160px] truncate">{{ $task->title }}</td>
                        <td class="px-3 py-2.5 text-slate-500 max-w-[120px] truncate">{{ $task->project?->name ?? '—' }}</td>
                        <td class="px-3 py-2.5 text-red-500 font-mono text-xs whitespace-nowrap">{{ $task->due_date?->format('d M Y') }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            </div>
        @endif
    </div>

    {{-- Pending Leave Requests --}}
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="flex items-center justify-between px-5 py-3 border-b border-slate-100">
            <h3 class="text-sm font-semibold text-slate-700">Pending Leave Requests</h3>
            <a href="{{ route('leave-requests.index') }}" class="text-xs text-[#E26B3D] hover:underline">View all</a>
        </div>
        @if($pendingLeaveRequests->isEmpty())
            <p class="px-5 py-8 text-sm text-slate-400 text-center">No pending leave requests</p>
        @else
            <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr class="text-xs text-slate-400 uppercase border-b border-slate-100">
                    <th class="px-5 py-2 text-left font-medium">Employee</th>
                    <th class="px-3 py-2 text-left font-medium">Type</th>
                    <th class="px-3 py-2 text-left font-medium">Dates</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-50">
                @foreach($pendingLeaveRequests as $lr)
                    <tr class="hover:bg-stone-50 transition-colors">
                        <td class="px-5 py-2.5 font-medium text-slate-800">{{ $lr->employee?->user?->name ?? '—' }}</td>
                        <td class="px-3 py-2.5 text-slate-500">{{ $lr->leaveType?->name ?? '—' }}</td>
                        <td class="px-3 py-2.5 text-slate-500 font-mono text-xs whitespace-nowrap">
                            {{ $lr->start_date?->format('d M') }} – {{ $lr->end_date?->format('d M Y') }}
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            </div>
        @endif
    </div>

</div>

{{-- Chart.js --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
(function () {
    const orange = '#E26B3D';
    const blue   = '#3b82f6';
    const green  = '#10b981';
    const amber  = '#f59e0b';
    const purple = '#8b5cf6';
    const slate  = '#94a3b8';
    const red    = '#ef4444';

    // ── Project status donut ───────────────────────────────────────────────
    const psByStatus = @json($projectsByStatus);
    const psLabels   = Object.keys(psByStatus).map(s => s.replace('_', ' '));
    const psValues   = Object.values(psByStatus);
    const psColors   = [blue, orange, amber, green, slate];

    new Chart(document.getElementById('projectStatusChart'), {
        type: 'doughnut',
        data: { labels: psLabels, datasets: [{ data: psValues, backgroundColor: psColors, borderWidth: 0 }] },
        options: {
            responsive: true, maintainAspectRatio: false, cutout: '65%',
            plugins: { legend: { position: 'bottom', labels: { font: { size: 11 }, padding: 10 } } },
        }
    });

    // ── Task status bar ────────────────────────────────────────────────────
    const tsByStatus = @json($tasksByStatus);
    const tsLabels   = Object.keys(tsByStatus).map(s => s.replace('_', ' '));
    const tsValues   = Object.values(tsByStatus);
    const tsColors   = [blue, orange, purple, green, red];

    new Chart(document.getElementById('taskStatusChart'), {
        type: 'bar',
        data: { labels: tsLabels, datasets: [{ label: 'Tasks', data: tsValues, backgroundColor: tsColors, borderRadius: 6 }] },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } }, x: { grid: { display: false } } },
        }
    });

    // ── Monthly expenses line ──────────────────────────────────────────────
    const expData = @json($expensesByMonth);

    new Chart(document.getElementById('expensesChart'), {
        type: 'line',
        data: {
            labels: expData.labels,
            datasets: [{
                label: 'Expenses', data: expData.values,
                borderColor: orange, backgroundColor: orange + '20',
                fill: true, tension: 0.4, pointRadius: 4,
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true }, x: { grid: { display: false } } },
        }
    });

    // ── Revenue vs Expenses ────────────────────────────────────────────────
    const revData = @json($revenueByMonth);

    new Chart(document.getElementById('revenueChart'), {
        type: 'line',
        data: {
            labels: revData.labels,
            datasets: [
                {
                    label: 'Revenue', data: revData.values,
                    borderColor: green, backgroundColor: green + '20',
                    fill: false, tension: 0.4, pointRadius: 4,
                },
                {
                    label: 'Expenses', data: expData.values,
                    borderColor: red, backgroundColor: red + '15',
                    fill: false, tension: 0.4, pointRadius: 4,
                },
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom', labels: { font: { size: 11 }, padding: 10 } } },
            scales: { y: { beginAtZero: true }, x: { grid: { display: false } } },
        }
    });
})();
</script>

@endsection

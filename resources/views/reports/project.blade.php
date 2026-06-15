<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Project Report</title>
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1e293b; padding: 32px; }
  .header { border-bottom: 2px solid #E26B3D; padding-bottom: 12px; margin-bottom: 20px; }
  .header h1 { font-size: 22px; color: #E26B3D; }
  .header h2 { font-size: 15px; color: #0f1b3d; margin-top: 4px; }
  .header p { font-size: 11px; color: #64748b; margin-top: 2px; }
  .meta { display: flex; flex-wrap: wrap; gap: 20px; margin-bottom: 20px; }
  .meta-item label { font-size: 10px; text-transform: uppercase; color: #94a3b8; display: block; }
  .meta-item span { font-weight: bold; font-size: 13px; }
  .section-title { font-size: 13px; font-weight: bold; color: #0f1b3d; border-bottom: 1px solid #e2e8f0; padding-bottom: 6px; margin: 20px 0 10px; }
  table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
  th { background: #0f1b3d; color: #fff; padding: 7px 9px; text-align: left; font-size: 10px; text-transform: uppercase; letter-spacing: .5px; }
  td { padding: 7px 9px; border-bottom: 1px solid #e2e8f0; }
  tr:nth-child(even) td { background: #f8fafc; }
  .stats-row { display: flex; gap: 16px; margin-bottom: 16px; }
  .stat-box { flex: 1; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px; text-align: center; }
  .stat-box .val { font-size: 22px; font-weight: bold; color: #0f1b3d; }
  .stat-box .lbl { font-size: 10px; color: #94a3b8; margin-top: 2px; text-transform: uppercase; }
  .badge { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 10px; font-weight: bold; }
  .badge-done { background: #dcfce7; color: #166534; }
  .badge-in_progress { background: #dbeafe; color: #1d4ed8; }
  .badge-todo { background: #f1f5f9; color: #64748b; }
  .badge-blocked { background: #fee2e2; color: #991b1b; }
  .badge-review { background: #fef3c7; color: #92400e; }
  .footer { margin-top: 20px; font-size: 10px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 10px; }
</style>
</head>
<body>

<div class="header">
  <h1>Project Report</h1>
  <h2>{{ $project->name }}</h2>
  <p>{{ config('app.name') }} &mdash; Generated {{ now()->format('d M Y H:i') }}</p>
</div>

<div class="meta">
  <div class="meta-item"><label>Client</label><span>{{ $project->client?->company_name ?? '—' }}</span></div>
  <div class="meta-item"><label>Status</label><span>{{ ucfirst(str_replace('_', ' ', $project->status->value ?? $project->status)) }}</span></div>
  <div class="meta-item"><label>Priority</label><span>{{ ucfirst($project->priority->value ?? $project->priority) }}</span></div>
  <div class="meta-item"><label>Start Date</label><span>{{ $project->start_date?->format('d M Y') ?? '—' }}</span></div>
  <div class="meta-item"><label>Due Date</label><span>{{ $project->due_date?->format('d M Y') ?? '—' }}</span></div>
  <div class="meta-item"><label>Budget</label><span>{{ $project->budget ? number_format((float)$project->budget, 2) : '—' }}</span></div>
  <div class="meta-item"><label>Created By</label><span>{{ $project->creator?->name ?? '—' }}</span></div>
</div>

{{-- Task summary --}}
<div class="stats-row">
  <div class="stat-box"><div class="val">{{ $taskStats['total'] }}</div><div class="lbl">Total Tasks</div></div>
  <div class="stat-box"><div class="val">{{ $taskStats['done'] }}</div><div class="lbl">Completed</div></div>
  <div class="stat-box"><div class="val">{{ $taskStats['total'] - $taskStats['done'] }}</div><div class="lbl">Remaining</div></div>
  <div class="stat-box"><div class="val">{{ $taskStats['overdue'] }}</div><div class="lbl">Overdue</div></div>
  <div class="stat-box"><div class="val">{{ $project->members->count() }}</div><div class="lbl">Members</div></div>
</div>

{{-- Team members --}}
@if($project->members->isNotEmpty())
<div class="section-title">Team Members</div>
<table>
  <thead><tr><th>Name</th><th>Role</th></tr></thead>
  <tbody>
    @foreach($project->members as $m)
    <tr><td>{{ $m->user?->name ?? '—' }}</td><td>{{ ucfirst($m->role_in_project->value ?? $m->role_in_project) }}</td></tr>
    @endforeach
  </tbody>
</table>
@endif

{{-- Tasks --}}
@if($project->tasks->isNotEmpty())
<div class="section-title">Tasks</div>
<table>
  <thead><tr><th>Task</th><th>Status</th><th>Priority</th><th>Due Date</th></tr></thead>
  <tbody>
    @foreach($project->tasks as $task)
    <tr>
      <td>{{ $task->title }}</td>
      <td><span class="badge badge-{{ $task->status->value ?? $task->status }}">{{ ucfirst(str_replace('_', ' ', $task->status->value ?? $task->status)) }}</span></td>
      <td>{{ ucfirst($task->priority->value ?? $task->priority) }}</td>
      <td>{{ $task->due_date?->format('d M Y') ?? '—' }}</td>
    </tr>
    @endforeach
  </tbody>
</table>
@endif

@if($project->description)
<div class="section-title">Description</div>
<p style="font-size:11px; color:#475569; line-height:1.6;">{{ $project->description }}</p>
@endif

<div class="footer">This is a system-generated report from {{ config('app.name') }}.</div>

</body>
</html>

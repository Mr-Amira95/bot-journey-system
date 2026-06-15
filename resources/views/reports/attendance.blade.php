<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Attendance Report</title>
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1e293b; padding: 32px; }
  .header { border-bottom: 2px solid #E26B3D; padding-bottom: 12px; margin-bottom: 20px; }
  .header h1 { font-size: 22px; color: #E26B3D; }
  .header p { font-size: 11px; color: #64748b; margin-top: 2px; }
  .meta { display: flex; gap: 32px; margin-bottom: 20px; }
  .meta-item label { font-size: 10px; text-transform: uppercase; color: #94a3b8; display: block; }
  .meta-item span { font-weight: bold; font-size: 13px; }
  table { width: 100%; border-collapse: collapse; }
  th { background: #0f1b3d; color: #fff; padding: 8px 10px; text-align: left; font-size: 10px; text-transform: uppercase; letter-spacing: .5px; }
  td { padding: 8px 10px; border-bottom: 1px solid #e2e8f0; }
  tr:nth-child(even) td { background: #f8fafc; }
  .footer { margin-top: 20px; font-size: 10px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 10px; }
</style>
</head>
<body>

<div class="header">
  <h1>Attendance Report</h1>
  <p>{{ config('app.name') }} &mdash; Generated {{ now()->format('d M Y H:i') }}</p>
</div>

<div class="meta">
  <div class="meta-item">
    <label>Employee</label>
    <span>{{ $employee->user?->name ?? '—' }}</span>
  </div>
  <div class="meta-item">
    <label>Department</label>
    <span>{{ $employee->department?->name ?? '—' }}</span>
  </div>
  <div class="meta-item">
    <label>Month</label>
    <span>{{ $monthLabel }}</span>
  </div>
  <div class="meta-item">
    <label>Total Records</label>
    <span>{{ $records->count() }}</span>
  </div>
</div>

@if($records->isEmpty())
  <p style="color:#94a3b8; text-align:center; padding:32px 0;">No attendance records found for this period.</p>
@else
<table>
  <thead>
    <tr>
      <th>Date</th>
      <th>Time</th>
      <th>Type</th>
      <th>Notes</th>
    </tr>
  </thead>
  <tbody>
    @foreach($records as $record)
    <tr>
      <td>{{ $record->time_date?->format('d M Y') }}</td>
      <td>{{ $record->time_date?->format('H:i') }}</td>
      <td>{{ ucfirst(str_replace('_', ' ', $record->type->value ?? $record->type)) }}</td>
      <td>{{ $record->notes ?? '—' }}</td>
    </tr>
    @endforeach
  </tbody>
</table>
@endif

<div class="footer">This is a system-generated report from {{ config('app.name') }}.</div>

</body>
</html>

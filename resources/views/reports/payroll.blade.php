<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Payroll Report</title>
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
  .amount { text-align: right; font-family: monospace; }
  .totals td { font-weight: bold; background: #f1f5f9 !important; border-top: 2px solid #0f1b3d; }
  .badge { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 10px; font-weight: bold; }
  .badge-draft { background: #e2e8f0; color: #64748b; }
  .badge-approved { background: #dcfce7; color: #166534; }
  .badge-paid { background: #dbeafe; color: #1d4ed8; }
  .footer { margin-top: 20px; font-size: 10px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 10px; }
</style>
</head>
<body>

<div class="header">
  <h1>Payroll Report</h1>
  <p>{{ config('app.name') }} &mdash; Generated {{ now()->format('d M Y H:i') }}</p>
</div>

<div class="meta">
  <div class="meta-item">
    <label>Period</label>
    <span>{{ $payrollRun->period_start->format('d M Y') }} &ndash; {{ $payrollRun->period_end->format('d M Y') }}</span>
  </div>
  <div class="meta-item">
    <label>Status</label>
    <span class="badge badge-{{ $payrollRun->status->value ?? $payrollRun->status }}">
      {{ ucfirst($payrollRun->status->value ?? $payrollRun->status) }}
    </span>
  </div>
  <div class="meta-item">
    <label>Created By</label>
    <span>{{ $payrollRun->createdBy?->name ?? '—' }}</span>
  </div>
  @if($payrollRun->approvedBy)
  <div class="meta-item">
    <label>Approved By</label>
    <span>{{ $payrollRun->approvedBy->name }}</span>
  </div>
  @endif
</div>

<table>
  <thead>
    <tr>
      <th>#</th>
      <th>Employee</th>
      <th class="amount">Base Salary</th>
      <th class="amount">Bonuses</th>
      <th class="amount">Deductions</th>
      <th class="amount">Net Salary</th>
    </tr>
  </thead>
  <tbody>
    @foreach($payrollRun->items as $i => $item)
    <tr>
      <td>{{ $i + 1 }}</td>
      <td>{{ $item->employee?->user?->name ?? '—' }}</td>
      <td class="amount">{{ number_format((float)$item->base_salary, 2) }}</td>
      <td class="amount">{{ number_format((float)$item->bonuses, 2) }}</td>
      <td class="amount">{{ number_format((float)$item->deductions, 2) }}</td>
      <td class="amount">{{ number_format((float)$item->net_salary, 2) }}</td>
    </tr>
    @endforeach
    <tr class="totals">
      <td colspan="2">TOTAL ({{ $payrollRun->items->count() }} employees)</td>
      <td class="amount">{{ number_format($payrollRun->items->sum('base_salary'), 2) }}</td>
      <td class="amount">{{ number_format($payrollRun->items->sum('bonuses'), 2) }}</td>
      <td class="amount">{{ number_format($payrollRun->items->sum('deductions'), 2) }}</td>
      <td class="amount">{{ number_format($payrollRun->items->sum('net_salary'), 2) }}</td>
    </tr>
  </tbody>
</table>

@if($payrollRun->notes)
<p style="margin-top:16px; font-size:11px; color:#64748b;"><strong>Notes:</strong> {{ $payrollRun->notes }}</p>
@endif

<div class="footer">This is a system-generated report from {{ config('app.name') }}.</div>

</body>
</html>

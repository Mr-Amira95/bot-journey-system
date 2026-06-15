<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Expenses Report</title>
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1e293b; padding: 28px; }
  .header { border-bottom: 2px solid #E26B3D; padding-bottom: 10px; margin-bottom: 16px; }
  .header h1 { font-size: 20px; color: #E26B3D; }
  .header p { font-size: 10px; color: #64748b; margin-top: 2px; }
  table { width: 100%; border-collapse: collapse; }
  th { background: #0f1b3d; color: #fff; padding: 7px 9px; text-align: left; font-size: 9px; text-transform: uppercase; letter-spacing: .5px; }
  td { padding: 7px 9px; border-bottom: 1px solid #e2e8f0; }
  tr:nth-child(even) td { background: #f8fafc; }
  .amount { text-align: right; font-family: monospace; }
  .totals td { font-weight: bold; background: #f1f5f9 !important; border-top: 2px solid #0f1b3d; }
  .badge { display: inline-block; padding: 1px 7px; border-radius: 4px; font-size: 9px; font-weight: bold; }
  .badge-pending { background: #fef3c7; color: #92400e; }
  .badge-approved { background: #dcfce7; color: #166534; }
  .badge-rejected { background: #fee2e2; color: #991b1b; }
  .badge-paid { background: #dbeafe; color: #1d4ed8; }
  .footer { margin-top: 16px; font-size: 9px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 8px; }
</style>
</head>
<body>

<div class="header">
  <h1>Expenses Report</h1>
  <p>{{ config('app.name') }} &mdash; Generated {{ now()->format('d M Y H:i') }}</p>
</div>

<table>
  <thead>
    <tr>
      <th>Date</th>
      <th>Title</th>
      <th>Category</th>
      <th>Project</th>
      <th>Paid By</th>
      <th class="amount">Amount</th>
      <th>Status</th>
    </tr>
  </thead>
  <tbody>
    @foreach($expenses as $expense)
    <tr>
      <td style="white-space:nowrap;">{{ $expense->expense_date?->format('d M Y') }}</td>
      <td>{{ $expense->title }}</td>
      <td>{{ $expense->category?->name ?? '—' }}</td>
      <td>{{ $expense->project?->name ?? '—' }}</td>
      <td>{{ $expense->paidBy?->name ?? '—' }}</td>
      <td class="amount">{{ number_format((float)$expense->amount, 2) }}</td>
      <td><span class="badge badge-{{ $expense->status->value ?? $expense->status }}">{{ ucfirst($expense->status->value ?? $expense->status) }}</span></td>
    </tr>
    @endforeach
    <tr class="totals">
      <td colspan="5">TOTAL ({{ $expenses->count() }} records)</td>
      <td class="amount">{{ number_format((float)$total, 2) }}</td>
      <td></td>
    </tr>
  </tbody>
</table>

<div class="footer">This is a system-generated report from {{ config('app.name') }}.</div>

</body>
</html>

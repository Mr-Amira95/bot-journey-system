<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>BRD - {{ $brd->title }}</title>
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
  .section-text { font-size: 11px; color: #475569; line-height: 1.6; white-space: pre-line; }
  .badge { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 10px; font-weight: bold; }
  .badge-approved { background: #dcfce7; color: #166534; }
  .badge-pending { background: #fef3c7; color: #92400e; }
  .badge-rejected { background: #fee2e2; color: #991b1b; }
  .footer { margin-top: 20px; font-size: 10px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 10px; }
</style>
</head>
<body>

<div class="header">
  <h1>Business Requirements Document</h1>
  <h2>{{ $brd->title }}</h2>
  <p>{{ config('app.name') }} &mdash; Generated {{ now()->format('d M Y H:i') }}</p>
</div>

<div class="meta">
  <div class="meta-item"><label>Project</label><span>{{ $brd->project?->name ?? '—' }}</span></div>
  <div class="meta-item"><label>Priority</label><span>{{ ucfirst($brd->priority->value ?? $brd->priority) }}</span></div>
  <div class="meta-item"><label>Status</label><span class="badge badge-{{ $brd->status->value ?? $brd->status }}">{{ ucfirst($brd->status->value ?? $brd->status) }}</span></div>
  <div class="meta-item"><label>Created By</label><span>{{ $brd->creator?->name ?? '—' }}</span></div>
  <div class="meta-item"><label>Created On</label><span>{{ $brd->created_at?->format('d M Y') ?? '—' }}</span></div>
  @if($brd->approver)
  <div class="meta-item"><label>{{ $brd->status->value === 'rejected' ? 'Rejected By' : 'Approved By' }}</label><span>{{ $brd->approver->name }}</span></div>
  <div class="meta-item"><label>Decision Date</label><span>{{ $brd->approved_at?->format('d M Y') ?? '—' }}</span></div>
  @endif
</div>

<div class="section-title">Description</div>
<p class="section-text">{{ $brd->description }}</p>

@if($brd->objective)
<div class="section-title">Business Objective</div>
<p class="section-text">{{ $brd->objective }}</p>
@endif

@if($brd->scope)
<div class="section-title">Scope</div>
<p class="section-text">{{ $brd->scope }}</p>
@endif

@if($brd->stakeholders)
<div class="section-title">Stakeholders</div>
<p class="section-text">{{ $brd->stakeholders }}</p>
@endif

@if($brd->status->value === 'rejected' && $brd->rejection_reason)
<div class="section-title">Rejection Reason</div>
<p class="section-text">{{ $brd->rejection_reason }}</p>
@endif

<div class="footer">This is a system-generated document from {{ config('app.name') }}.</div>

</body>
</html>

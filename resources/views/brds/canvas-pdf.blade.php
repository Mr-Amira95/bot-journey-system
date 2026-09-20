<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>AI Canvas - {{ $canvas['use_case_name'] ?? $brd->title }}</title>
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1e293b; padding: 26px; }

  .header { border-bottom: 3px solid #E26B3D; padding-bottom: 12px; margin-bottom: 16px; }
  .header .eyebrow { font-size: 10px; text-transform: uppercase; letter-spacing: 1px; color: #E26B3D; font-weight: bold; }
  .header h1 { font-size: 20px; color: #0f1b3d; margin-top: 4px; }
  .header p { font-size: 10.5px; color: #64748b; margin-top: 3px; }

  .summary { background: #f8fafc; border-left: 3px solid #E26B3D; padding: 10px 12px; margin-bottom: 16px; font-size: 11px; line-height: 1.6; color: #334155; }

  .section-title { font-size: 10.5px; font-weight: bold; color: #0f1b3d; text-transform: uppercase; letter-spacing: .4px; margin-bottom: 6px; }
  .card { border: 1px solid #e2e8f0; border-radius: 4px; padding: 10px 12px; margin-bottom: 12px; }
  .col { width: 48%; }
  .col-left { float: left; }
  .col-right { float: right; }
  .row:after { content: ""; display: table; clear: both; }

  p.body-text { font-size: 10.5px; line-height: 1.6; color: #475569; }

  ul.list { list-style: none; }
  ul.list li { font-size: 10.5px; line-height: 1.6; color: #475569; padding-left: 12px; position: relative; margin-bottom: 3px; }
  ul.list li:before { content: "\2013"; position: absolute; left: 0; color: #E26B3D; font-weight: bold; }

  table.kpi-table { width: 100%; border-collapse: collapse; }
  table.kpi-table th { background: #0f1b3d; color: #fff; padding: 5px 8px; text-align: left; font-size: 9.5px; text-transform: uppercase; letter-spacing: .4px; }
  table.kpi-table td { padding: 5px 8px; border-bottom: 1px solid #e2e8f0; font-size: 10.5px; color: #334155; }
  table.kpi-table tr:nth-child(even) td { background: #f8fafc; }

  .badge-row { margin-top: 4px; }
  .badge { display: inline-block; padding: 3px 10px; border-radius: 10px; font-size: 10px; font-weight: bold; background: #0f1b3d; color: #fff; }
  .badge-note { font-size: 9.5px; color: #64748b; margin-left: 6px; }

  .platform-note { margin-top: 14px; background: #0f1b3d; color: #e2e8f0; border-radius: 4px; padding: 10px 12px; font-size: 10px; line-height: 1.6; }
  .platform-note strong { color: #fff; }

  .footer { margin-top: 16px; font-size: 9px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 8px; display: flex; justify-content: space-between; }
</style>
</head>
<body>

@php
    // Defensive normalization: Claude's tool-use output isn't schema-enforced, so older
    // stored canvases (generated before the service-side normalization) may hold a plain
    // string where a list was expected.
    foreach (['data_sources', 'roi_highlights', 'assumptions_risks', 'next_steps'] as $field) {
        $value = $canvas[$field] ?? null;
        $canvas[$field] = is_array($value) ? array_values($value) : (is_string($value) && trim($value) !== '' ? [$value] : []);
    }
    $canvas['kpis'] = is_array($canvas['kpis'] ?? null) ? $canvas['kpis'] : [];
@endphp

<div class="header">
  <div class="eyebrow">AI Canvas &mdash; Al Tanfeethi</div>
  <h1>{{ $canvas['use_case_name'] ?? $brd->title }}</h1>
  <p>Source BRD: {{ $brd->title }} &middot; {{ $brd->project?->name ?? config('app.name') }} &middot; Generated {{ ($brd->ai_canvas_generated_at ?? now())->format('d M Y') }}</p>
</div>

<div class="summary">{{ $canvas['executive_summary'] ?? '' }}</div>

<div class="row">
  <div class="col col-left">
    <div class="card">
      <div class="section-title">Problem</div>
      <p class="body-text">{{ $canvas['problem_statement'] ?? '—' }}</p>
    </div>
  </div>
  <div class="col col-right">
    <div class="card">
      <div class="section-title">Proposed Solution</div>
      <p class="body-text">{{ $canvas['proposed_solution'] ?? '—' }}</p>
    </div>
  </div>
</div>

<div class="row">
  <div class="col col-left">
    <div class="card">
      <div class="section-title">Data Sources</div>
      @if(!empty($canvas['data_sources']))
      <ul class="list">
        @foreach($canvas['data_sources'] as $item)
        <li>{{ $item }}</li>
        @endforeach
      </ul>
      @else
      <p class="body-text">—</p>
      @endif
    </div>
  </div>
  <div class="col col-right">
    <div class="card">
      <div class="section-title">Potential ROI</div>
      @if(!empty($canvas['roi_highlights']))
      <ul class="list">
        @foreach($canvas['roi_highlights'] as $item)
        <li>{{ $item }}</li>
        @endforeach
      </ul>
      @else
      <p class="body-text">—</p>
      @endif
    </div>
  </div>
</div>

<div class="card">
  <div class="section-title">KPIs</div>
  @if(!empty($canvas['kpis']))
  <table class="kpi-table">
    <thead><tr><th>Metric</th><th>Target</th></tr></thead>
    <tbody>
      @foreach($canvas['kpis'] as $kpi)
      <tr>
        <td>{{ $kpi['metric'] ?? '—' }}</td>
        <td>{{ $kpi['target'] ?? '—' }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>
  @else
  <p class="body-text">—</p>
  @endif
</div>

<div class="row">
  <div class="col col-left">
    <div class="card">
      <div class="section-title">Delivery Timeline</div>
      <div class="badge-row">
        <span class="badge">{{ $canvas['timeline_estimate'] ?? '—' }}</span>
        <span class="badge-note">including testing &amp; validation</span>
      </div>
      <p class="body-text" style="margin-top:6px;">{{ $canvas['timeline_rationale'] ?? '' }}</p>
    </div>
  </div>
  <div class="col col-right">
    <div class="card">
      <div class="section-title">Assumptions &amp; Risks</div>
      @if(!empty($canvas['assumptions_risks']))
      <ul class="list">
        @foreach($canvas['assumptions_risks'] as $item)
        <li>{{ $item }}</li>
        @endforeach
      </ul>
      @else
      <p class="body-text">—</p>
      @endif
    </div>
  </div>
</div>

<div class="card">
  <div class="section-title">Next Steps</div>
  @if(!empty($canvas['next_steps']))
  <ul class="list">
    @foreach($canvas['next_steps'] as $item)
    <li>{{ $item }}</li>
    @endforeach
  </ul>
  @else
  <p class="body-text">—</p>
  @endif
</div>

<div class="platform-note">
  <strong>Delivery platform:</strong> Built and orchestrated on the existing Azure Databricks environment already deployed for Al Tanfeethi, extending the data pipelines and agents delivered in the foundation project.
</div>

<div class="footer">
  <span>{{ config('app.name') }} &mdash; AI Canvas</span>
  <span>Confidential &mdash; prepared for Al Tanfeethi</span>
</div>

</body>
</html>

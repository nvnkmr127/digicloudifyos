<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #111827; font-size: 12px; }
        .muted { color: #6b7280; }
        .h1 { font-size: 18px; font-weight: 700; margin: 0; }
        .grid { width: 100%; border-collapse: collapse; margin-top: 12px; }
        .grid th, .grid td { border: 1px solid #e5e7eb; padding: 10px; vertical-align: top; }
        .grid th { background: #f9fafb; text-align: left; font-weight: 700; }
        .kpi { font-size: 16px; font-weight: 700; margin-top: 4px; }
        .section { margin-top: 18px; }
        .row { display: flex; gap: 12px; }
        .card { border: 1px solid #e5e7eb; padding: 12px; border-radius: 8px; width: 32%; }
    </style>
</head>
<body>
    <div>
        <div class="h1">Report</div>
        <div class="muted">{{ $reportData['meta']['date_range'] ?? '' }}</div>
        <div class="muted">Generated {{ ($reportData['meta']['generated_at'] ?? now())->format('M d, Y g:i A') }}</div>
    </div>

    <div class="section row">
        <div class="card">
            <div class="muted">Total invoiced</div>
            <div class="kpi">${{ number_format((float) ($reportData['financial']['total_invoiced'] ?? 0), 2) }}</div>
        </div>
        <div class="card">
            <div class="muted">Total paid</div>
            <div class="kpi">${{ number_format((float) ($reportData['financial']['total_paid'] ?? 0), 2) }}</div>
        </div>
        <div class="card">
            <div class="muted">Pending</div>
            <div class="kpi">${{ number_format((float) ($reportData['financial']['pending_amount'] ?? 0), 2) }}</div>
        </div>
    </div>

    <table class="grid">
        <thead>
            <tr>
                <th style="width: 33%;">Delivery</th>
                <th style="width: 33%;">Clients</th>
                <th style="width: 34%;">Notes</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <div><span class="muted">Active projects:</span> <strong>{{ (int) ($reportData['performance']['active_projects'] ?? 0) }}</strong></div>
                    <div style="margin-top: 6px;"><span class="muted">Completed projects:</span> <strong>{{ (int) ($reportData['performance']['completed_projects'] ?? 0) }}</strong></div>
                    <div style="margin-top: 6px;"><span class="muted">Total hours:</span> <strong>{{ number_format((float) ($reportData['performance']['total_hours'] ?? 0), 1) }}</strong></div>
                </td>
                <td>
                    <div><span class="muted">Total clients:</span> <strong>{{ (int) ($reportData['clients']['total_clients'] ?? 0) }}</strong></div>
                    <div style="margin-top: 6px;"><span class="muted">New clients (range):</span> <strong>{{ (int) ($reportData['clients']['new_clients'] ?? 0) }}</strong></div>
                </td>
                <td class="muted">
                    This export reflects data available at generation time.
                </td>
            </tr>
        </tbody>
    </table>
</body>
</html>


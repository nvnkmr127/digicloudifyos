<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>{{ $template->name }}</title>
        <style>
            body { font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial; background: #f3f4f6; margin: 0; padding: 24px; }
            .card { background: #fff; border: 1px solid #e5e7eb; border-radius: 16px; padding: 20px; margin-bottom: 16px; }
            .muted { color: #6b7280; font-size: 12px; }
            .title { font-weight: 800; font-size: 18px; color: #111827; }
            .row { display: flex; gap: 12px; flex-wrap: wrap; }
            .metric { flex: 1 1 180px; background: #f9fafb; border: 1px solid #f3f4f6; border-radius: 14px; padding: 12px; }
            .metric .k { font-size: 12px; color: #6b7280; }
            .metric .v { font-weight: 800; font-size: 16px; color: #111827; margin-top: 4px; }
            .badge { display: inline-block; padding: 2px 10px; border-radius: 999px; font-size: 12px; font-weight: 700; background: #eef2ff; color: #3730a3; }
            .item { padding: 10px 12px; border: 1px solid #f3f4f6; border-radius: 14px; margin-top: 8px; }
        </style>
    </head>
    <body>
        <div class="card">
            <div class="muted">Deliverable</div>
            <div class="title">{{ $template->name }} — {{ $client->name }}</div>
            <div class="muted">Date: {{ $date }}</div>
        </div>

        <div class="card">
            <div class="title">Performance Snapshot</div>
            <div class="muted">Daily KPIs pulled from connected accounts.</div>
            <div class="row" style="margin-top: 12px;">
                @foreach($snapshots as $s)
                    <div class="metric">
                        <div class="k">{{ strtoupper($s->channel_type) }}</div>
                        <div class="v">ROAS: {{ number_format((float) $s->roas, 2) }}</div>
                        <div class="muted">Spend {{ number_format((float) $s->spend, 2) }} • Revenue {{ number_format((float) $s->revenue, 2) }}</div>
                    </div>
                @endforeach
                @if($snapshots->isEmpty())
                    <div class="muted">No snapshot data for this date.</div>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="title">SEO & Content Opportunities</div>
            <div class="muted">Derived from Search Console query/page analysis.</div>
            @foreach($seo as $o)
                <div class="item">
                    <div class="badge">{{ strtoupper($o->severity) }}</div>
                    <div style="margin-top: 6px; font-weight: 800; color: #111827;">{{ $o->title }}</div>
                    <div class="muted">{{ $o->opportunity_type }}</div>
                </div>
            @endforeach
            @if($seo->isEmpty())
                <div class="muted" style="margin-top: 10px;">No SEO opportunities for this date.</div>
            @endif
        </div>
    </body>
</html>


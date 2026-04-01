<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Performance Intelligence Briefing</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');
        body { font-family: 'Inter', -apple-system, sans-serif; background-color: #f9fafb; margin: 0; padding: 0; -webkit-font-smoothing: antialiased; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); padding: 32px; border-radius: 12px 12px 0 0; color: white; }
        .content { background: white; padding: 32px; border-radius: 0 0 12px 12px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
        .badge { display: inline-block; padding: 4px 12px; border-radius: 9999px; font-size: 12px; font-weight: 600; text-transform: uppercase; }
        .badge-urgent { background-color: #fee2e2; color: #991b1b; }
        .badge-important { background-color: #fef3c7; color: #92400e; }
        .badge-opportunity { background-color: #d1fae5; color: #065f46; }
        .item-card { border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px; margin-bottom: 16px; transition: all 0.2s; }
        .item-title { font-size: 16px; font-weight: 700; color: #111827; margin-bottom: 4px; }
        .item-client { font-size: 14px; color: #4b5563; font-weight: 600; text-transform: uppercase; margin-bottom: 12px; }
        .item-desc { font-size: 14px; color: #374151; line-height: 1.5; margin-bottom: 16px; }
        .action-box { background-color: #f3f4f6; border-radius: 6px; padding: 12px; font-size: 14px; border-left: 4px solid #4f46e5; }
        .footer { text-align: center; padding-top: 32px; font-size: 12px; color: #6b7280; }
        .btn { display: inline-block; background-color: #4f46e5; color: white; padding: 12px 24px; border-radius: 8px; font-weight: 600; text-decoration: none; margin-top: 24px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 style="margin: 0; font-size: 24px;">Daily Agency Briefing</h1>
            <p style="margin: 8px 0 0 0; opacity: 0.9;">{{ $briefing->briefing_date->format('l, M j, Y') }}</p>
        </div>

        <div class="content">
            <div style="display: flex; justify-content: space-between; margin-bottom: 32px;">
                <div style="text-align: center; flex: 1;">
                    <span style="display: block; font-size: 24px; font-weight: 800; color: #b91c1c;">{{ $briefing->critical_alerts_count }}</span>
                    <span style="font-size: 12px; color: #6b7280; text-transform: uppercase;">Urgent Issues</span>
                </div>
                <div style="text-align: center; flex: 1;">
                    <span style="display: block; font-size: 24px; font-weight: 800; color: #059669;">{{ $briefing->opportunities_count }}</span>
                    <span style="font-size: 12px; color: #6b7280; text-transform: uppercase;">Opportunities</span>
                </div>
                <div style="text-align: center; flex: 1;">
                    <span style="display: block; font-size: 24px; font-weight: 800; color: #111827;">{{ $briefing->total_clients_analyzed }}</span>
                    <span style="font-size: 12px; color: #6b7280; text-transform: uppercase;">Clients Analyzed</span>
                </div>
            </div>

            @if($urgentItems->count() > 0)
                <h2 style="font-size: 18px; color: #991b1b; border-bottom: 2px solid #fee2e2; padding-bottom: 8px; margin-bottom: 16px;">🔥 Urgent Interventions Required</h2>
                @foreach($urgentItems as $item)
                    <div class="item-card" style="border-left: 4px solid #ef4444;">
                        <div class="item-client">{{ $item->client->name }}</div>
                        <div class="item-title">{{ $item->title }}</div>
                        <p class="item-desc">{{ $item->description }}</p>
                        <div class="action-box">
                            <strong>Recommended Action:</strong> {{ $item->action }}
                        </div>
                    </div>
                @endforeach
            @endif

            @if($importantItems->count() > 0)
                <h2 style="font-size: 18px; color: #92400e; border-bottom: 2px solid #fef3c7; padding-bottom: 8px; margin-top: 32px; margin-bottom: 16px;">⚡ Performance Insights</h2>
                @foreach($importantItems as $item)
                    <div class="item-card" style="border-left: 4px solid #f59e0b;">
                        <div class="item-client">{{ $item->client->name }}</div>
                        <div class="item-title">{{ $item->title }}</div>
                        <p class="item-desc">{{ $item->description }}</p>
                        <div class="action-box">
                            <strong>Next Step:</strong> {{ $item->action }}
                        </div>
                    </div>
                @endforeach
            @endif

            @if($opportunities->count() > 0)
                <h2 style="font-size: 18px; color: #065f46; border-bottom: 2px solid #d1fae5; padding-bottom: 8px; margin-top: 32px; margin-bottom: 16px;">🚀 Growth Opportunities</h2>
                @foreach($opportunities as $item)
                    <div class="item-card" style="border-left: 4px solid #10b981;">
                        <div class="item-client">{{ $item->client->name }}</div>
                        <div class="item-title">{{ $item->title }}</div>
                        <p class="item-desc">{{ $item->description }}</p>
                        <div class="action-box">
                            <strong>Action:</strong> {{ $item->action }}
                        </div>
                    </div>
                @endforeach
            @endif

            <div style="text-align: center;">
                <a href="{{ url('/intelligence/briefing/' . $briefing->id) }}" class="btn">View Full Briefing Dashboard</a>
            </div>
        </div>

        <div class="footer">
            &copy; {{ date('Y') }} DigiCloudify Intelligence. All rights reserved.<br>
            You are receiving this because you are an admin of {{ $briefing->organization->name }}.
        </div>
    </div>
</body>
</html>

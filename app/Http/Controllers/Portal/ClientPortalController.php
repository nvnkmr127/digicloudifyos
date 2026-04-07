<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\AiInsight;
use App\Models\Client;
use App\Models\DailyBriefing;
use App\Models\PerformanceSnapshot;
use Illuminate\Http\Request;

class ClientPortalController extends Controller
{
    public function show(Request $request, Client $client)
    {
        $date = now()->subDay()->toDateString();

        $snapshots = PerformanceSnapshot::where('organization_id', $client->organization_id)
            ->where('client_id', $client->id)
            ->whereDate('snapshot_date', $date)
            ->orderBy('channel_type')
            ->get();

        $briefing = DailyBriefing::where('organization_id', $client->organization_id)
            ->whereDate('briefing_date', $date)
            ->first();

        $actionItems = $briefing
            ? $briefing->actionItems()->where('client_id', $client->id)->orderBy('sort_order')->get()
            : collect();

        $insights = AiInsight::where('organization_id', $client->organization_id)
            ->where('client_id', $client->id)
            ->whereDate('insight_date', $date)
            ->where('is_dismissed', false)
            ->orderByRaw("CASE priority WHEN 'critical' THEN 1 WHEN 'high' THEN 2 WHEN 'medium' THEN 3 WHEN 'low' THEN 4 WHEN 'opportunity' THEN 5 ELSE 6 END")
            ->get();

        return view('portal.client', [
            'client' => $client,
            'date' => $date,
            'snapshots' => $snapshots,
            'actionItems' => $actionItems,
            'insights' => $insights,
        ]);
    }
}

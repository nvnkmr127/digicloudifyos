<?php

namespace App\Livewire\Intelligence;

use App\Models\AiInsight;
use App\Models\Client;
use App\Models\ClientChannelConnection;
use App\Models\ClientHealthScore;
use App\Models\PerformanceAnomaly;
use App\Models\PerformanceSnapshot;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class ClientWorkspace extends Component
{
    public Client $client;

    /** @var '1d'|'7d'|'30d' */
    public string $dateRange = '7d';

    public function mount(Client $client): void
    {
        $this->client = $client;

        abort_unless(auth()->check(), 401);
        Gate::authorize('view', $this->client);
    }

    public function setDateRange(string $range): void
    {
        if (! in_array($range, ['1d', '7d', '30d'], true)) {
            return;
        }

        $this->dateRange = $range;
    }

    public function render()
    {
        $orgId = auth()->user()->organization_id;
        $clientId = $this->client->id;

        [$start, $end] = match ($this->dateRange) {
            '1d' => [today()->subDays(1), today()],
            '30d' => [today()->subDays(30), today()],
            default => [today()->subDays(7), today()],
        };

        $cacheKey = "intelligence.client_workspace.{$orgId}.{$clientId}.{$this->dateRange}";

        $data = Cache::remember($cacheKey, 600, function () use ($orgId, $clientId, $start, $end) {
            $connections = ClientChannelConnection::where('organization_id', $orgId)
                ->where('client_id', $clientId)
                ->where('is_active', true)
                ->orderBy('channel_type')
                ->get();

            $snapshots = PerformanceSnapshot::where('organization_id', $orgId)
                ->where('client_id', $clientId)
                ->whereBetween('snapshot_date', [$start->toDateString(), $end->toDateString()])
                ->orderBy('snapshot_date', 'desc')
                ->get()
                ->groupBy('channel_type');

            $healthScores = ClientHealthScore::where('organization_id', $orgId)
                ->where('client_id', $clientId)
                ->orderBy('score_date', 'desc')
                ->limit(30)
                ->get()
                ->reverse()
                ->values();

            $anomalies = PerformanceAnomaly::where('organization_id', $orgId)
                ->where('client_id', $clientId)
                ->unresolved()
                ->orderByRaw("CASE severity WHEN 'critical' THEN 1 WHEN 'high' THEN 2 WHEN 'medium' THEN 3 WHEN 'low' THEN 4 ELSE 5 END")
                ->latest('detected_at')
                ->limit(10)
                ->get();

            $insights = AiInsight::where('organization_id', $orgId)
                ->where('client_id', $clientId)
                ->active()
                ->latest('insight_date')
                ->limit(10)
                ->get();

            return compact('connections', 'snapshots', 'healthScores', 'anomalies', 'insights');
        });

        return view('livewire.intelligence.client-workspace', [
            ...$data,
            'start' => $start,
            'end' => $end,
        ])->layout('layouts.app');
    }
}


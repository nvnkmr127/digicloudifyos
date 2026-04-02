<?php

namespace App\Livewire\Intelligence;

use App\Models\Client;
use App\Models\ClientHealthScore;
use App\Models\PerformanceSnapshot;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class ClientPerformanceCenter extends Component
{
    public Client $client;

    public $date;

    public $snapshots;

    public $healthTrend;

    public function mount(Client $client)
    {
        $this->client = $client;
        $this->date = today()->toDateString();
        $this->loadData();
    }

    public function loadData()
    {
        $clientId = $this->client->id;
        $date = $this->date;

        $this->snapshots = Cache::remember("client_snapshots_{$clientId}_{$date}", 600, function () use ($clientId, $date) {
            return PerformanceSnapshot::where('client_id', $clientId)
                ->where('snapshot_date', $date)
                ->get();
        });

        $this->healthTrend = Cache::remember("client_health_trend_{$clientId}", 600, function () use ($clientId) {
            return ClientHealthScore::where('client_id', $clientId)
                ->orderBy('score_date', 'asc')
                ->limit(30)
                ->get();
        });
    }

    public function render()
    {
        return view('livewire.intelligence.client-performance-center')
            ->layout('layouts.app');
    }
}

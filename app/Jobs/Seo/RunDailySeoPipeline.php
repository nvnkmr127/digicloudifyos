<?php

namespace App\Jobs\Seo;

use App\Models\ClientChannelConnection;
use App\Models\Organization;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RunDailySeoPipeline implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public ?string $date = null)
    {
        $this->onQueue('intelligence');
    }

    public function handle(): void
    {
        $date = $this->date ?? now()->subDay()->toDateString();

        foreach (Organization::all() as $org) {
            $clients = ClientChannelConnection::where('organization_id', $org->id)
                ->where('channel_type', 'google_search_console')
                ->active()
                ->get(['client_id'])
                ->groupBy('client_id');

            foreach ($clients as $clientId => $rows) {
                SyncSearchConsoleDimensions::dispatch($org->id, (string) $clientId, $date);
            }
        }

        ComputeSeoOpportunities::dispatch($date)->delay(now()->addMinutes(20));
        ComputeAeoGeoLocalOpportunities::dispatch($date)->delay(now()->addMinutes(25));
    }
}

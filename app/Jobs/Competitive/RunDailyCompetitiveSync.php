<?php

namespace App\Jobs\Competitive;

use App\Models\ClientCompetitor;
use App\Models\Organization;
use App\Models\SocialListeningSource;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RunDailyCompetitiveSync implements ShouldQueue
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
            $metaCompetitors = ClientCompetitor::where('organization_id', $org->id)
                ->where('platform', 'meta_page')
                ->where('is_active', true)
                ->get(['client_id'])
                ->groupBy('client_id');

            foreach ($metaCompetitors as $clientId => $rows) {
                SyncMetaAdLibraryCompetitors::dispatch($org->id, (string) $clientId, $date);
            }

            $socialClients = SocialListeningSource::where('organization_id', $org->id)
                ->where('is_active', true)
                ->get(['client_id'])
                ->groupBy('client_id');

            foreach ($socialClients as $clientId => $rows) {
                SyncSocialListeningSources::dispatch($org->id, (string) $clientId, $date);
            }
        }
    }
}

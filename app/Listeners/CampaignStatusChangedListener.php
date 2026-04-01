<?php

namespace App\Listeners;

use App\Events\CampaignStatusChanged;
use App\Models\CreativeRequest;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class CampaignStatusChangedListener
{
    /**
     * Handle the event.
     */
    public function handle(CampaignStatusChanged $event): void
    {
        $campaign = $event->campaign;
        
        if ($campaign->status === 'creative_requested') {
            Log::info('Triggering creative request automation for campaign: ' . $campaign->id);
            
            CreativeRequest::create([
                'organization_id' => $campaign->organization_id,
                'campaign_id' => $campaign->id,
                'client_id' => $campaign->client_id,
                'title' => 'Initial Creative Set: ' . $campaign->name,
                'description' => 'Automatic request generated via Campaign Board status shift to Creative Requested. Please analyst campaign goals and provide a diversified ad set.',
                'status' => 'requested',
                'priority' => 'High',
                'due_date' => now()->addDays(3),
            ]);
        }
    }
}

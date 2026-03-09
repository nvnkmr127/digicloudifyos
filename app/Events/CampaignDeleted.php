<?php

namespace App\Events;

use App\Models\Campaign;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CampaignDeleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Campaign $campaign) {}

    public function broadcastOn(): array
    {
        return [
            new PresenceChannel('organization.'.$this->campaign->organization_id),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->campaign->id,
            'name' => $this->campaign->name,
        ];
    }

    public function broadcastAs(): string
    {
        return 'campaign.deleted';
    }
}

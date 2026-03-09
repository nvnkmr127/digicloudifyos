<?php

namespace App\Events;

use App\Models\Campaign;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CampaignCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Campaign $campaign) {}

    public function broadcastOn(): array
    {
        return [
            new PresenceChannel('organization.'.$this->campaign->organization_id),
            new Channel('campaigns'),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->campaign->id,
            'name' => $this->campaign->name,
            'status' => $this->campaign->status,
            'client' => $this->campaign->client?->name,
            'created_at' => $this->campaign->created_at->toISOString(),
        ];
    }

    public function broadcastAs(): string
    {
        return 'campaign.created';
    }
}

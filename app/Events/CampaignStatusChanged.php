<?php

namespace App\Events;

use App\Models\Campaign;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CampaignStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Campaign $campaign,
        public string $oldStatus
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PresenceChannel('organization.'.$this->campaign->organization_id),
            new Channel('campaigns.'.$this->campaign->id),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->campaign->id,
            'name' => $this->campaign->name,
            'old_status' => $this->oldStatus,
            'new_status' => $this->campaign->status,
            'updated_at' => $this->campaign->updated_at->toISOString(),
        ];
    }

    public function broadcastAs(): string
    {
        return 'campaign.status.changed';
    }
}

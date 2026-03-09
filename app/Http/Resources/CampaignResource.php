<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CampaignResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'external_campaign_id' => $this->external_campaign_id,
            'objective' => $this->objective,
            'status' => $this->status,
            'start_date' => $this->start_date?->format('Y-m-d'),
            'end_date' => $this->end_date?->format('Y-m-d'),
            'daily_budget' => $this->daily_budget,
            'lifetime_budget' => $this->lifetime_budget,
            'formatted_budget' => $this->formatted_budget,
            'is_running' => $this->isRunning(),
            'is_completed' => $this->isCompleted(),
            'client' => $this->whenLoaded('client', fn () => [
                'id' => $this->client->id,
                'name' => $this->client->name,
            ]),
            'ad_account' => $this->whenLoaded('adAccount', fn () => [
                'id' => $this->adAccount->id,
                'account_name' => $this->adAccount->account_name,
                'platform' => $this->adAccount->platform,
            ]),
            'tasks_count' => $this->whenCounted('tasks'),
            'creative_requests_count' => $this->whenCounted('creativeRequests'),
            'alerts_count' => $this->whenCounted('alerts'),
            'tasks' => TaskResource::collection($this->whenLoaded('tasks')),
            'creative_requests' => CreativeRequestResource::collection($this->whenLoaded('creativeRequests')),
            'alerts' => AlertResource::collection($this->whenLoaded('alerts')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}

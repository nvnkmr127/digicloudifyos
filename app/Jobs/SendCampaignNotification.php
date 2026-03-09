<?php

namespace App\Jobs;

use App\Models\Campaign;
use App\Models\User;
use App\Notifications\CampaignNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendCampaignNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public $timeout = 60;

    public function __construct(
        public Campaign $campaign,
        public string $type,
        public array $data = []
    ) {
        $this->onQueue('notifications');
    }

    public function handle(): void
    {
        try {
            $users = User::where('organization_id', $this->campaign->organization_id)
                ->whereHas('roles', function ($query) {
                    $query->whereIn('name', ['admin', 'manager']);
                })
                ->get();

            foreach ($users as $user) {
                $user->notify(new CampaignNotification(
                    $this->campaign,
                    $this->type,
                    $this->data
                ));
            }

            Log::info('Campaign notification sent', [
                'campaign_id' => $this->campaign->id,
                'type' => $this->type,
                'users_count' => $users->count(),
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send campaign notification', [
                'campaign_id' => $this->campaign->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}

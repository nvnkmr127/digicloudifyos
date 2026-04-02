<?php

namespace App\Jobs\Compliance;

use App\Models\Client;
use App\Models\ClientChannelConnection;
use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class IntegrationHealthCheck implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public ?string $date = null)
    {
        $this->onQueue('intelligence');
    }

    public function handle(): void
    {
        $date = $this->date ?? now()->subDay()->toDateString();

        $connections = ClientChannelConnection::active()
            ->whereIn('last_sync_status', ['failed', 'disabled'])
            ->orWhereNull('last_synced_at')
            ->with('client')
            ->get();

        foreach ($connections as $c) {
            $client = $c->client;
            if (! $client instanceof Client) {
                continue;
            }

            $needs = false;
            if ($c->last_sync_status === 'failed') {
                $needs = true;
            }
            if (! $c->last_synced_at) {
                $needs = true;
            }
            if ($c->last_synced_at && $c->last_synced_at->lt(now()->subDays(2))) {
                $needs = true;
            }

            if (! $needs) {
                continue;
            }

            $exists = Task::where('organization_id', $c->organization_id)
                ->where('client_id', $client->id)
                ->where('title', 'Fix integration sync')
                ->where('status', '!=', 'completed')
                ->where('created_at', '>=', now()->subDays(7))
                ->exists();

            if ($exists) {
                continue;
            }

            Task::create([
                'organization_id' => $c->organization_id,
                'client_id' => $client->id,
                'title' => 'Fix integration sync',
                'description' => 'Integration '.$c->channel_type.' is not syncing reliably (status: '.($c->last_sync_status ?: 'unknown').'). Reconnect credentials and verify access.',
                'status' => 'pending',
                'priority' => 'high',
                'deadline' => now()->addDays(1),
            ]);
        }
    }
}

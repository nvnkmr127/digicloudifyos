<?php

namespace App\Jobs\Playbooks;

use App\Models\Client;
use App\Models\Organization;
use App\Models\PlaybookTemplate;
use App\Services\Playbooks\PlaybookService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RunOnboardingPlaybooks implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public ?string $date = null)
    {
        $this->onQueue('intelligence');
    }

    public function handle(PlaybookService $service): void
    {
        $date = $this->date ?? now()->toDateString();

        foreach (Organization::all() as $org) {
            $service->ensureDefaults($org->id);

            $templates = PlaybookTemplate::where('organization_id', $org->id)
                ->where('category', 'onboarding')
                ->where('is_active', true)
                ->get();

            if ($templates->isEmpty()) {
                continue;
            }

            $clients = Client::where('organization_id', $org->id)
                ->where('status', 'ACTIVE')
                ->where('created_at', '>=', now()->subDays(2))
                ->get();

            foreach ($clients as $client) {
                foreach ($templates as $template) {
                    $service->runTemplateForClient($org->id, $client, $template, $date);
                }
            }
        }
    }
}

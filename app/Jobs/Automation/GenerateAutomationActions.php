<?php

namespace App\Jobs\Automation;

use App\Models\Client;
use App\Models\Organization;
use App\Services\Automation\AutomationEngine;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateAutomationActions implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public ?string $date = null)
    {
        $this->onQueue('intelligence');
    }

    public function handle(AutomationEngine $engine): void
    {
        $date = $this->date ?? now()->subDay()->toDateString();

        foreach (Organization::lazy() as $org) {
            $clients = Client::where('organization_id', $org->id)->active()->get(['id']);
            foreach ($clients as $client) {
                $engine->runForClient($org->id, $client->id, $date);
            }
        }
    }
}

<?php

namespace App\Jobs\Operations;

use App\Models\Organization;
use App\Services\ProductivityAnalyticsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ComputeDailyProductivity implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public ?string $date = null)
    {
        $this->onQueue('intelligence');
    }

    public function handle(ProductivityAnalyticsService $service): void
    {
        $date = $this->date ?? now()->subDay()->toDateString();

        foreach (Organization::lazy() as $org) {
            $service->computeForOrganization($org->id, $date);
        }
    }
}

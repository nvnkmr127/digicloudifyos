<?php

namespace App\Jobs\Intelligence;

use App\Models\Organization;
use App\Services\Intelligence\PerformanceMonitorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class FetchClientPerformanceData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 3600;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        $this->onQueue('intelligence');
    }

    /**
     * Execute the job.
     */
    public function handle(PerformanceMonitorService $monitor): void
    {
        Log::info('FetchClientPerformanceData job started.');
        $date = now()->subDay()->toDateString();

        foreach (Organization::lazy() as $org) {
            $monitor->runForOrganization($org->id, $date);
        }

        Log::info('FetchClientPerformanceData job completed.');
    }
}

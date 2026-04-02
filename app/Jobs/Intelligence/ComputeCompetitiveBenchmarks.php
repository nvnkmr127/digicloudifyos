<?php

namespace App\Jobs\Intelligence;

use App\Models\Organization;
use App\Services\Intelligence\CompetitiveBenchmarkService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ComputeCompetitiveBenchmarks implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public ?string $date = null)
    {
        $this->onQueue('intelligence');
    }

    public function handle(CompetitiveBenchmarkService $benchmarks): void
    {
        $date = $this->date ?? now()->subDay()->toDateString();

        foreach (Organization::all() as $org) {
            $benchmarks->runForOrganization($org->id, $date);
        }
    }
}

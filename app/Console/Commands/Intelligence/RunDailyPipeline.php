<?php

namespace App\Console\Commands\Intelligence;

use App\Jobs\Intelligence\FetchClientPerformanceData;
use App\Jobs\Intelligence\GenerateAiInsights;
use App\Jobs\Intelligence\GenerateDailyBriefing;
use App\Jobs\Intelligence\RunAnomalyDetection;
use App\Jobs\Intelligence\SendDailyBriefingEmail;
use Illuminate\Console\Command;

class RunDailyPipeline extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'intelligence:run-pipeline {--org= : Optional organization ID} {--step=all : Step to run: fetch, anomalies, insights, briefing, email, all}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manually triggers the performance intelligence pipeline steps.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $step = $this->option('step');
        $orgId = $this->option('org');

        $this->info("Starting Intelligence Pipeline: Step = {$step}");

        if ($step === 'all' || $step === 'fetch') {
            $this->info('Dispatching FetchClientPerformanceData...');
            FetchClientPerformanceData::dispatchSync();
        }

        if ($step === 'all' || $step === 'anomalies') {
            $this->info('Evaluating Anomalies...');
            RunAnomalyDetection::dispatchSync();
        }

        if ($step === 'all' || $step === 'insights') {
            $this->info('Generating AI Insights...');
            GenerateAiInsights::dispatchSync();
        }

        if ($step === 'all' || $step === 'briefing') {
            $this->info('Compiling Daily Briefings...');
            GenerateDailyBriefing::dispatchSync();
        }

        if ($step === 'all' || $step === 'email') {
            $this->info('Sending Briefing Emails...');
            SendDailyBriefingEmail::dispatchSync();
        }

        $this->info('Intelligence Pipeline Finished.');
    }
}

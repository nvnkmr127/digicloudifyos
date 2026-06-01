<?php

namespace App\Jobs\Intelligence;

use App\Models\PerformanceAnomaly;
use App\Models\PerformanceSnapshot;
use App\Services\Intelligence\AnomalyDetectionService;
use App\Services\Intelligence\PerformanceMonitorService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RunAnomalyDetection implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
    public function handle(AnomalyDetectionService $detector, PerformanceMonitorService $monitor): void
    {
        Log::info('RunAnomalyDetection cleanup job started.');

        $date = now()->subDay()->toDateString();

        // Read today's snapshots where anomaly_flags is NULL
        $snapshots = PerformanceSnapshot::whereDate('snapshot_date', $date)
            ->whereNull('anomaly_flags')
            ->get();

        foreach ($snapshots as $snapshot) {
            $metrics = [
                'impressions' => $snapshot->impressions,
                'clicks' => $snapshot->clicks,
                'spend' => (float) $snapshot->spend,
                'conversions' => (float) $snapshot->conversions,
                'revenue' => (float) $snapshot->revenue,
                'ctr' => (float) $snapshot->ctr,
                'cpc' => (float) $snapshot->cpc,
                'roas' => (float) $snapshot->roas,
                'engagement_rate' => (float) $snapshot->engagement_rate,
                'leads' => $snapshot->leads,
            ];

            $baselines = [
                'ctr' => (float) $snapshot->baseline_ctr,
                'cpc' => (float) $snapshot->baseline_cpc,
                'roas' => (float) $snapshot->baseline_roas,
                'leads' => (float) $snapshot->baseline_leads,
            ];

            $anomalies = $detector->detect($metrics, $baselines, $snapshot->client_id, $snapshot->organization_id, $snapshot->channel_type);

            $anomalyTypes = [];
            foreach ($anomalies as $anomalyData) {
                $anomalyTypes[] = $anomalyData['anomaly_type'];
                PerformanceAnomaly::updateOrCreate(
                    [
                        'organization_id' => $anomalyData['organization_id'],
                        'client_id' => $anomalyData['client_id'],
                        'channel_type' => $anomalyData['channel_type'],
                        'anomaly_type' => $anomalyData['anomaly_type'],
                        'snapshot_id' => $snapshot->id,
                    ],
                    array_merge($anomalyData, [
                        'detected_at' => Carbon::parse($date)->endOfDay(),
                    ])
                );
            }

            $snapshot->update(['anomaly_flags' => $anomalyTypes]);
        }

        Log::info('RunAnomalyDetection cleanup job completed. Processed snapshots: '.$snapshots->count());
    }
}

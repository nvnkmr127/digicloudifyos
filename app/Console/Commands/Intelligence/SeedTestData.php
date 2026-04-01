<?php

namespace App\Console\Commands\Intelligence;

use App\Models\Client;
use App\Models\PerformanceSnapshot;
use App\Models\PerformanceAnomaly;
use App\Models\ClientHealthScore;
use App\Models\AiInsight;
use App\Models\DailyBriefing;
use App\Models\BriefingActionItem;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class SeedTestData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'intelligence:seed-test-data {--org= : Organization ID to seed for} {--days=7 : Number of days of data}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed the database with mock intelligence data for testing dashboards.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $orgId = $this->option('org') ?: Client::first()?->organization_id;
        $days = (int) $this->option('days');

        if (!$orgId) {
            $this->error("No organization found to seed.");
            return;
        }

        $clients = Client::where('organization_id', $orgId)->get();
        if ($clients->isEmpty()) {
            $this->error("No clients found in organization {$orgId}.");
            return;
        }

        $this->info("Seeding data for " . $clients->count() . " clients over {$days} days...");

        foreach ($clients as $client) {
            for ($i = $days; $i >= 0; $i--) {
                $date = Carbon::today()->subDays($i);
                
                // 1. Snapshot
                $channels = ['meta_ads', 'google_ads', 'organic_social'];
                foreach ($channels as $channel) {
                    $snapshot = PerformanceSnapshot::create([
                        'organization_id' => $orgId,
                        'client_id' => $client->id,
                        'channel_type' => $channel,
                        'snapshot_date' => $date->toDateString(),
                        'spend' => rand(100, 1000),
                        'clicks' => rand(500, 2000),
                        'impressions' => rand(10000, 50000),
                        'conversions' => rand(10, 50),
                        'leads' => rand(5, 20),
                        'revenue' => rand(1000, 5000),
                        'ctr' => rand(1, 5) / 100,
                        'cpc' => rand(50, 200) / 100,
                        'roas' => rand(20, 80) / 10,
                        'baseline_ctr' => 0.02,
                        'baseline_cpc' => 1.20,
                        'baseline_roas' => 4.5,
                        'baseline_leads' => 12,
                    ]);

                    // 2. Anomaly (10% chance)
                    if (rand(1, 10) === 1) {
                        PerformanceAnomaly::create([
                            'organization_id' => $orgId,
                            'client_id' => $client->id,
                            'snapshot_id' => $snapshot->id,
                            'channel_type' => $channel,
                            'anomaly_type' => 'performance_drop',
                            'metric_name' => 'CTR',
                            'baseline_value' => 0.02,
                            'current_value' => 0.01,
                            'deviation_percentage' => -50,
                            'severity' => $i === 0 ? 'critical' : 'high',
                            'detected_at' => $date->setHour(2),
                        ]);
                    }
                }

                // 3. Health Score
                ClientHealthScore::create([
                    'organization_id' => $orgId,
                    'client_id' => $client->id,
                    'score_date' => $date->toDateString(),
                    'overall_score' => rand(60, 95),
                    'ads_health' => rand(50, 100),
                    'organic_health' => rand(50, 100),
                    'lead_health' => rand(50, 100),
                    'funnel_health' => rand(50, 100),
                    'trend' => rand(1, 2) === 1 ? 'improving' : 'stable',
                    'metrics_summary' => ['note' => 'Generated via seeder'],
                ]);
            }

            // 4. Insights (Latest)
            AiInsight::create([
                'organization_id' => $orgId,
                'client_id' => $client->id,
                'insight_date' => today()->toDateString(),
                'priority' => 'high',
                'category' => 'issue',
                'title' => 'Sudden Meta CTR Decay',
                'issue_description' => 'Meta CTR has plummeted by 50% compared to the 7-day average.',
                'root_cause' => 'Creative fatigue detected in the main Retargeting campaign.',
                'recommended_action' => 'Refresh video assets and pause low-performing static ads.',
                'expected_impact' => 'high',
                'effort_level' => 'medium',
            ]);
        }

        // 5. Daily Briefing (Today)
        $briefing = DailyBriefing::create([
            'organization_id' => $orgId,
            'briefing_date' => today()->toDateString(),
            'status' => 'ready',
            'summary' => 'System-wide health is strong with 2 critical interventions required.',
            'total_clients_analyzed' => $clients->count(),
            'critical_alerts_count' => 2,
            'opportunities_count' => 5,
        ]);

        foreach ($clients->take(3) as $c) {
            BriefingActionItem::create([
                'daily_briefing_id' => $briefing->id,
                'client_id' => $c->id,
                'priority_level' => 'urgent',
                'category' => 'budget',
                'title' => 'Scale Top-Performing Google Campaign',
                'description' => $c->name . ' is seeing ROAS above 12x on Google Search.',
                'action' => 'Increase daily budget by 20% immediately.',
            ]);
        }

        $this->info("Seeding complete. Intelligence Lab ready for testing.");
    }
}

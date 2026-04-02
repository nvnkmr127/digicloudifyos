<?php

namespace App\Jobs\Seo;

use App\Models\Client;
use App\Models\Organization;
use App\Models\SearchConsoleDimensionRow;
use App\Models\SeoOpportunity;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ComputeSeoOpportunities implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public ?string $date = null)
    {
        $this->onQueue('intelligence');
    }

    public function handle(): void
    {
        $date = $this->date ?? now()->subDay()->toDateString();

        foreach (Organization::all() as $org) {
            $clients = Client::where('organization_id', $org->id)->active()->get(['id', 'organization_id']);
            foreach ($clients as $client) {
                $this->computeForClient($org->id, $client->id, $date);
            }
        }
    }

    protected function computeForClient(string $orgId, string $clientId, string $date): void
    {
        $this->lowCtrHighImpressions($orgId, $clientId, $date);
        $this->page2Opportunities($orgId, $clientId, $date);
        $this->contentDecay($orgId, $clientId, $date);
    }

    protected function lowCtrHighImpressions(string $orgId, string $clientId, string $date): void
    {
        $rows = SearchConsoleDimensionRow::where('organization_id', $orgId)
            ->where('client_id', $clientId)
            ->whereDate('metric_date', $date)
            ->where('dimension', 'query')
            ->where('impressions', '>=', 200)
            ->orderByDesc('impressions')
            ->limit(50)
            ->get();

        $candidates = $rows->filter(function ($r) {
            $ctr = $r->ctr !== null ? (float) $r->ctr : null;
            $pos = $r->avg_position !== null ? (float) $r->avg_position : null;

            return $ctr !== null && $pos !== null && $pos <= 10 && $ctr < 0.01;
        })->take(10)->values();

        if ($candidates->isEmpty()) {
            return;
        }

        $title = 'Improve CTR for high-impression queries';

        SeoOpportunity::create([
            'organization_id' => $orgId,
            'client_id' => $clientId,
            'opportunity_date' => $date,
            'opportunity_type' => 'low_ctr_high_impressions',
            'title' => $title,
            'severity' => 'medium',
            'payload' => [
                'queries' => $candidates->map(fn ($r) => [
                    'query' => $r->key,
                    'impressions' => (int) $r->impressions,
                    'clicks' => (int) $r->clicks,
                    'ctr' => (float) $r->ctr,
                    'avg_position' => (float) $r->avg_position,
                ])->all(),
            ],
        ]);

        $this->createTaskIfMissing($orgId, $clientId, $title, 'Update titles/meta descriptions to better match intent and improve SERP CTR.', 'medium', $date);
    }

    protected function page2Opportunities(string $orgId, string $clientId, string $date): void
    {
        $rows = SearchConsoleDimensionRow::where('organization_id', $orgId)
            ->where('client_id', $clientId)
            ->whereDate('metric_date', $date)
            ->where('dimension', 'query')
            ->where('impressions', '>=', 200)
            ->orderByDesc('impressions')
            ->limit(100)
            ->get();

        $candidates = $rows->filter(function ($r) {
            $pos = $r->avg_position !== null ? (float) $r->avg_position : null;

            return $pos !== null && $pos >= 11 && $pos <= 20;
        })->take(15)->values();

        if ($candidates->isEmpty()) {
            return;
        }

        $title = 'Push page-2 queries to page 1';

        SeoOpportunity::create([
            'organization_id' => $orgId,
            'client_id' => $clientId,
            'opportunity_date' => $date,
            'opportunity_type' => 'position_11_20',
            'title' => $title,
            'severity' => 'high',
            'payload' => [
                'queries' => $candidates->map(fn ($r) => [
                    'query' => $r->key,
                    'impressions' => (int) $r->impressions,
                    'clicks' => (int) $r->clicks,
                    'ctr' => $r->ctr !== null ? (float) $r->ctr : null,
                    'avg_position' => (float) $r->avg_position,
                ])->all(),
            ],
        ]);

        $this->createTaskIfMissing($orgId, $clientId, $title, 'Improve on-page content and internal links for these queries to reach top-10.', 'high', $date);
    }

    protected function contentDecay(string $orgId, string $clientId, string $date): void
    {
        $end = Carbon::parse($date)->endOfDay();
        $w1Start = $end->copy()->subDays(6)->startOfDay()->toDateString();
        $w1End = $end->toDateString();
        $w0Start = $end->copy()->subDays(13)->startOfDay()->toDateString();
        $w0End = $end->copy()->subDays(7)->toDateString();

        $w1 = SearchConsoleDimensionRow::where('organization_id', $orgId)
            ->where('client_id', $clientId)
            ->where('dimension', 'page')
            ->whereBetween('metric_date', [$w1Start, $w1End])
            ->selectRaw('`key`, SUM(clicks) as clicks_sum, SUM(impressions) as imp_sum')
            ->groupBy('key')
            ->get()
            ->keyBy('key');

        $w0 = SearchConsoleDimensionRow::where('organization_id', $orgId)
            ->where('client_id', $clientId)
            ->where('dimension', 'page')
            ->whereBetween('metric_date', [$w0Start, $w0End])
            ->selectRaw('`key`, SUM(clicks) as clicks_sum, SUM(impressions) as imp_sum')
            ->groupBy('key')
            ->get()
            ->keyBy('key');

        $drops = [];
        foreach ($w1 as $page => $row1) {
            $row0 = $w0->get($page);
            if (! $row0) {
                continue;
            }
            $c1 = (int) ($row1->clicks_sum ?? 0);
            $c0 = (int) ($row0->clicks_sum ?? 0);
            if ($c0 < 20) {
                continue;
            }
            if ($c1 >= $c0) {
                continue;
            }
            $dropPct = (($c0 - $c1) / $c0) * 100;
            if ($dropPct < 25) {
                continue;
            }
            $drops[] = [
                'page' => $page,
                'clicks_prev_7d' => $c0,
                'clicks_last_7d' => $c1,
                'drop_pct' => $dropPct,
            ];
        }

        usort($drops, fn ($a, $b) => $b['drop_pct'] <=> $a['drop_pct']);
        $drops = array_slice($drops, 0, 10);

        if (empty($drops)) {
            return;
        }

        $title = 'Content decay detected on key pages';

        SeoOpportunity::create([
            'organization_id' => $orgId,
            'client_id' => $clientId,
            'opportunity_date' => $date,
            'opportunity_type' => 'content_decay',
            'title' => $title,
            'severity' => 'high',
            'payload' => [
                'pages' => $drops,
                'window_prev' => [$w0Start, $w0End],
                'window_last' => [$w1Start, $w1End],
            ],
        ]);

        $this->createTaskIfMissing($orgId, $clientId, $title, 'Refresh content, update internal links, and check SERP changes for these URLs.', 'high', $date);
    }

    protected function createTaskIfMissing(string $orgId, string $clientId, string $title, string $description, string $priority, string $date): void
    {
        $exists = Task::where('organization_id', $orgId)
            ->where('client_id', $clientId)
            ->where('title', $title)
            ->where('status', '!=', 'completed')
            ->where('created_at', '>=', Carbon::parse($date)->subDays(7))
            ->exists();

        if ($exists) {
            return;
        }

        Task::create([
            'organization_id' => $orgId,
            'client_id' => $clientId,
            'title' => $title,
            'description' => $description,
            'status' => 'pending',
            'priority' => $priority,
            'deadline' => Carbon::parse($date)->addDays(3),
        ]);
    }
}

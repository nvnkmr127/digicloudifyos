<?php

namespace App\Jobs\Seo;

use App\Models\Client;
use App\Models\GoogleBusinessProfileDailyMetric;
use App\Models\SearchConsoleDimensionRow;
use App\Models\SeoOpportunity;
use App\Models\SeoSiteAuditIssue;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ComputeAeoGeoLocalOpportunities implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public ?string $date = null)
    {
        $this->onQueue('intelligence');
    }

    public function handle(): void
    {
        $date = $this->date ?? now()->subDay()->toDateString();

        $clients = Client::where('status', 'ACTIVE')->get(['id', 'organization_id']);

        foreach ($clients as $client) {
            $this->aeoQuestionQueries($client->organization_id, $client->id, $date);
            $this->localIntentQueries($client->organization_id, $client->id, $date);
            $this->geoTrustSignals($client->organization_id, $client->id, $date);
            $this->gbpConversionGap($client->organization_id, $client->id, $date);
        }
    }

    protected function aeoQuestionQueries(string $orgId, string $clientId, string $date): void
    {
        $rows = SearchConsoleDimensionRow::where('organization_id', $orgId)
            ->where('client_id', $clientId)
            ->whereDate('metric_date', $date)
            ->where('dimension', 'query')
            ->where('impressions', '>=', 150)
            ->orderByDesc('impressions')
            ->limit(200)
            ->get();

        $questionWords = ['how', 'what', 'why', 'when', 'where', 'who', 'can', 'does', 'best', 'vs', 'difference'];

        $candidates = $rows->filter(function ($r) use ($questionWords) {
            $q = strtolower((string) $r->key);
            foreach ($questionWords as $w) {
                if (str_starts_with($q, $w.' ') || str_contains($q, ' '.$w.' ')) {
                    return true;
                }
            }

            return false;
        })->filter(function ($r) {
            $pos = $r->avg_position !== null ? (float) $r->avg_position : null;

            return $pos !== null && $pos >= 6 && $pos <= 25;
        })->take(15)->values();

        if ($candidates->isEmpty()) {
            return;
        }

        $title = 'AEO: build FAQ/snippet answers for question queries';

        SeoOpportunity::create([
            'organization_id' => $orgId,
            'client_id' => $clientId,
            'opportunity_date' => $date,
            'opportunity_type' => 'aeo_question_queries',
            'title' => $title,
            'severity' => 'high',
            'payload' => [
                'queries' => $candidates->map(fn ($r) => [
                    'query' => $r->key,
                    'impressions' => (int) $r->impressions,
                    'clicks' => (int) $r->clicks,
                    'ctr' => $r->ctr !== null ? (float) $r->ctr : null,
                    'avg_position' => $r->avg_position !== null ? (float) $r->avg_position : null,
                ])->all(),
            ],
        ]);

        $this->createTaskIfMissing($orgId, $clientId, $title, 'Create concise Q&A content sections targeting these queries and add FAQPage/HowTo schema where applicable.', 'high', $date);
    }

    protected function localIntentQueries(string $orgId, string $clientId, string $date): void
    {
        $rows = SearchConsoleDimensionRow::where('organization_id', $orgId)
            ->where('client_id', $clientId)
            ->whereDate('metric_date', $date)
            ->where('dimension', 'query')
            ->where('impressions', '>=', 100)
            ->orderByDesc('impressions')
            ->limit(250)
            ->get();

        $candidates = $rows->filter(function ($r) {
            $q = strtolower((string) $r->key);
            if (str_contains($q, 'near me')) {
                return true;
            }
            if (str_contains($q, 'nearby')) {
                return true;
            }
            if (preg_match('/\\bin\\s+[a-z]{3,}/', $q)) {
                return true;
            }
            if (preg_match('/\\bbest\\s+[a-z]{3,}\\s+in\\s+[a-z]{3,}/', $q)) {
                return true;
            }

            return false;
        })->take(20)->values();

        if ($candidates->isEmpty()) {
            return;
        }

        $title = 'Local SEO: capture high-intent location queries';

        SeoOpportunity::create([
            'organization_id' => $orgId,
            'client_id' => $clientId,
            'opportunity_date' => $date,
            'opportunity_type' => 'local_intent_queries',
            'title' => $title,
            'severity' => 'high',
            'payload' => [
                'queries' => $candidates->map(fn ($r) => [
                    'query' => $r->key,
                    'impressions' => (int) $r->impressions,
                    'clicks' => (int) $r->clicks,
                    'avg_position' => $r->avg_position !== null ? (float) $r->avg_position : null,
                ])->all(),
            ],
        ]);

        $this->createTaskIfMissing($orgId, $clientId, $title, 'Create/optimize location pages and align Google Business Profile categories/services to these queries.', 'high', $date);
    }

    protected function geoTrustSignals(string $orgId, string $clientId, string $date): void
    {
        $issues = SeoSiteAuditIssue::where('organization_id', $orgId)
            ->where('client_id', $clientId)
            ->whereDate('created_at', $date)
            ->whereIn('issue_type', ['missing_org_schema', 'missing_faq_schema', 'missing_localbusiness_schema'])
            ->get();

        if ($issues->isEmpty()) {
            return;
        }

        $title = 'GEO: improve entity trust signals (schema + on-site structure)';

        SeoOpportunity::create([
            'organization_id' => $orgId,
            'client_id' => $clientId,
            'opportunity_date' => $date,
            'opportunity_type' => 'geo_trust_signals',
            'title' => $title,
            'severity' => 'medium',
            'payload' => [
                'issues' => $issues->map(fn ($i) => [
                    'issue_type' => $i->issue_type,
                    'severity' => $i->severity,
                    'title' => $i->title,
                ])->values()->all(),
            ],
        ]);

        $this->createTaskIfMissing($orgId, $clientId, $title, 'Add Organization/LocalBusiness schema, FAQ schema where relevant, and strengthen About/Contact/author signals for LLM visibility.', 'medium', $date);
    }

    protected function gbpConversionGap(string $orgId, string $clientId, string $date): void
    {
        $m = GoogleBusinessProfileDailyMetric::where('organization_id', $orgId)
            ->where('client_id', $clientId)
            ->whereDate('metric_date', $date)
            ->first();

        if (! $m) {
            return;
        }

        $impressions = (int) ($m->impressions_search_desktop + $m->impressions_search_mobile + $m->impressions_maps_desktop + $m->impressions_maps_mobile);
        $actions = (int) ($m->website_clicks + $m->call_clicks + $m->directions_requests);

        if ($impressions < 200) {
            return;
        }
        if ($actions >= max(5, (int) round($impressions * 0.02))) {
            return;
        }

        $title = 'Google Business Profile: improve conversion actions';

        SeoOpportunity::create([
            'organization_id' => $orgId,
            'client_id' => $clientId,
            'opportunity_date' => $date,
            'opportunity_type' => 'gbp_conversion_gap',
            'title' => $title,
            'severity' => 'high',
            'payload' => [
                'impressions' => $impressions,
                'actions' => $actions,
                'website_clicks' => (int) $m->website_clicks,
                'call_clicks' => (int) $m->call_clicks,
                'directions_requests' => (int) $m->directions_requests,
            ],
        ]);

        $this->createTaskIfMissing($orgId, $clientId, $title, 'Update GBP: primary category, services, photos, business description, and add posts/offers; request reviews to raise trust and drive calls.', 'high', $date);
    }

    protected function createTaskIfMissing(string $orgId, string $clientId, string $title, string $description, string $priority, string $date): void
    {
        $exists = Task::where('organization_id', $orgId)
            ->where('client_id', $clientId)
            ->where('title', $title)
            ->where('status', '!=', 'completed')
            ->where('created_at', '>=', Carbon::parse($date)->subDays(14))
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
            'deadline' => Carbon::parse($date)->addDays(5),
        ]);
    }
}

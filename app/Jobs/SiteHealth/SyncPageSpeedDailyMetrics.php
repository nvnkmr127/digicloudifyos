<?php

namespace App\Jobs\SiteHealth;

use App\Models\Client;
use App\Models\Organization;
use App\Models\PageSpeedDailyMetric;
use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class SyncPageSpeedDailyMetrics implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [60, 300, 900];

    public function __construct(public ?string $date = null)
    {
        $this->onQueue('intelligence');
    }

    public function handle(): void
    {
        $key = config('services.pagespeed.api_key');
        if (! is_string($key) || $key === '') {
            return;
        }

        $date = $this->date ?? now()->subDay()->toDateString();

        foreach (Organization::lazy() as $org) {
            $clients = Client::where('organization_id', $org->id)
                ->active()
                ->whereNotNull('website_url')
                ->get(['id', 'organization_id', 'website_url']);

            foreach ($clients as $client) {
                $url = $this->normalizeUrl((string) $client->website_url);
                if (! $url) {
                    continue;
                }

                $mobile = $this->runPageSpeed($key, $url, 'mobile');
                $desktop = $this->runPageSpeed($key, $url, 'desktop');

                PageSpeedDailyMetric::updateOrCreate(
                    [
                        'organization_id' => $org->id,
                        'client_id' => $client->id,
                        'metric_date' => $date,
                        'url' => $url,
                    ],
                    [
                        'performance_mobile' => $mobile['performance'] ?? null,
                        'performance_desktop' => $desktop['performance'] ?? null,
                        'lcp_ms_mobile' => $mobile['lcp_ms'] ?? null,
                        'lcp_ms_desktop' => $desktop['lcp_ms'] ?? null,
                        'cls_mobile' => $mobile['cls'] ?? null,
                        'cls_desktop' => $desktop['cls'] ?? null,
                        'raw_data' => [
                            'mobile' => $mobile['raw'] ?? null,
                            'desktop' => $desktop['raw'] ?? null,
                        ],
                    ]
                );

                $this->createAlerts($org->id, $client->id, $date, $mobile['performance'] ?? null, $desktop['performance'] ?? null);
            }
        }
    }

    protected function runPageSpeed(string $key, string $url, string $strategy): array
    {
        $resp = Http::get('https://www.googleapis.com/pagespeedonline/v5/runPagespeed', [
            'url' => $url,
            'strategy' => $strategy,
            'category' => 'performance',
            'key' => $key,
        ]);

        if ($resp->failed()) {
            throw new \RuntimeException('PageSpeed request failed.');
        }

        $json = $resp->json();
        $lighthouse = $json['lighthouseResult'] ?? [];
        $categories = is_array($lighthouse) ? ($lighthouse['categories'] ?? []) : [];
        $perf = is_array($categories) ? ($categories['performance']['score'] ?? null) : null;
        $perfPct = is_numeric($perf) ? (int) round(((float) $perf) * 100) : null;

        $audits = is_array($lighthouse) ? ($lighthouse['audits'] ?? []) : [];
        $lcp = is_array($audits) ? ($audits['largest-contentful-paint']['numericValue'] ?? null) : null;
        $cls = is_array($audits) ? ($audits['cumulative-layout-shift']['numericValue'] ?? null) : null;

        return [
            'performance' => $perfPct,
            'lcp_ms' => is_numeric($lcp) ? (float) $lcp : null,
            'cls' => is_numeric($cls) ? (float) $cls : null,
            'raw' => $json,
        ];
    }

    protected function createAlerts(string $orgId, string $clientId, string $date, ?int $mobile, ?int $desktop): void
    {
        $score = $mobile ?? $desktop;
        if ($score === null) {
            return;
        }

        if ($score >= 60) {
            return;
        }

        $title = 'Fix page speed';

        $exists = Task::where('organization_id', $orgId)
            ->where('client_id', $clientId)
            ->where('title', $title)
            ->where('status', '!=', 'completed')
            ->where('created_at', '>=', now()->subDays(7))
            ->exists();

        if ($exists) {
            return;
        }

        Task::create([
            'organization_id' => $orgId,
            'client_id' => $clientId,
            'title' => $title,
            'description' => 'PageSpeed score is low (mobile: '.($mobile ?? '-').', desktop: '.($desktop ?? '-').'). Reduce LCP/CLS and optimize assets.',
            'status' => 'pending',
            'priority' => $score < 40 ? 'high' : 'medium',
            'deadline' => now()->addDays(5),
        ]);
    }

    protected function normalizeUrl(string $url): ?string
    {
        $url = trim($url);
        if ($url === '') {
            return null;
        }
        if (! str_starts_with($url, 'http://') && ! str_starts_with($url, 'https://')) {
            $url = 'https://'.$url;
        }

        return filter_var($url, FILTER_VALIDATE_URL) ? $url : null;
    }
}

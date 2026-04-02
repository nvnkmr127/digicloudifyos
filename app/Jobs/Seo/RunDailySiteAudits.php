<?php

namespace App\Jobs\Seo;

use App\Models\Client;
use App\Models\SeoSiteAuditIssue;
use App\Models\Task;
use App\Services\Seo\SiteAuditService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RunDailySiteAudits implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public ?string $date = null)
    {
        $this->onQueue('intelligence');
    }

    public function handle(SiteAuditService $service): void
    {
        $date = $this->date ?? now()->subDay()->toDateString();

        $clients = Client::where('status', 'ACTIVE')->whereNotNull('website_url')->get(['id', 'organization_id', 'website_url']);

        foreach ($clients as $client) {
            $audit = $service->run($client->organization_id, $client->id, $date, (string) $client->website_url);
            if (! $audit) {
                continue;
            }

            $issues = SeoSiteAuditIssue::where('seo_site_audit_id', $audit->id)
                ->where(function ($q) {
                    $q->whereIn('severity', ['critical', 'high'])
                        ->orWhereIn('issue_type', ['missing_localbusiness_schema', 'missing_title', 'noindex_detected']);
                })
                ->get();

            foreach ($issues as $issue) {
                $this->createTask($client->organization_id, $client->id, $issue);
            }
        }
    }

    protected function createTask(string $orgId, string $clientId, SeoSiteAuditIssue $issue): void
    {
        $title = 'SEO audit fix: '.$issue->issue_type;

        $exists = Task::where('organization_id', $orgId)
            ->where('client_id', $clientId)
            ->where('title', $title)
            ->where('status', '!=', 'completed')
            ->where('created_at', '>=', now()->subDays(14))
            ->exists();

        if ($exists) {
            return;
        }

        Task::create([
            'organization_id' => $orgId,
            'client_id' => $clientId,
            'title' => $title,
            'description' => ($issue->title ?: 'Fix SEO issue').($issue->url ? (' • URL: '.$issue->url) : ''),
            'status' => 'pending',
            'priority' => $issue->severity === 'critical' ? 'urgent' : 'high',
            'deadline' => now()->addDays($issue->severity === 'critical' ? 2 : 5),
        ]);
    }
}

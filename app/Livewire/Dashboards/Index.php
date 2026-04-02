<?php

namespace App\Livewire\Dashboards;

use App\Models\BrandKit;
use App\Models\Client;
use App\Models\ClientPlaybookRun;
use App\Models\DashboardLayout;
use App\Models\MetaAdLibraryDailySummary;
use App\Models\PerformanceAnomaly;
use App\Models\PerformanceSnapshot;
use App\Models\PlaybookRunTask;
use App\Models\ProductivityDailySummary;
use App\Models\SeoSiteAuditIssue;
use App\Models\User;
use App\Services\AnalyticsService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Index extends Component
{
    public string $date;

    public array $widgets = [];

    public function mount(): void
    {
        $this->date = now()->subDay()->toDateString();
    }

    public function render(AnalyticsService $analytics)
    {
        $user = Auth::user();
        if (! $user instanceof User) {
            abort(403);
        }

        $layout = DashboardLayout::where('organization_id', $user->organization_id)
            ->where('user_id', $user->id)
            ->where('is_default', true)
            ->first();

        $this->widgets = $layout?->widgets ?? [
            ['type' => 'org_kpis'],
            ['type' => 'roi_summary'],
            ['type' => 'productivity'],
        ];

        $orgKpis = $analytics->getDashboardMetrics($user->organization_id, '30days');

        $prod = ProductivityDailySummary::where('organization_id', $user->organization_id)
            ->whereDate('summary_date', $this->date)
            ->get();

        $competitiveAds = MetaAdLibraryDailySummary::where('organization_id', $user->organization_id)
            ->whereDate('metric_date', $this->date)
            ->sum('new_ads_count');

        $competitiveAnoms = PerformanceAnomaly::where('organization_id', $user->organization_id)
            ->whereDate('detected_at', $this->date)
            ->where('channel_type', 'competitive')
            ->whereNull('resolved_at')
            ->count();

        $clients = Client::where('organization_id', $user->organization_id)->active()->count();

        $snapshots = PerformanceSnapshot::where('organization_id', $user->organization_id)
            ->whereDate('snapshot_date', $this->date)
            ->count();

        $playbookRuns = ClientPlaybookRun::where('organization_id', $user->organization_id)
            ->whereDate('run_date', $this->date)
            ->count();

        $playbookTasksOpen = PlaybookRunTask::where('organization_id', $user->organization_id)
            ->whereHas('task', fn ($q) => $q->where('status', '!=', 'completed'))
            ->count();

        $seoIssues = SeoSiteAuditIssue::where('organization_id', $user->organization_id)
            ->whereIn('severity', ['critical', 'high'])
            ->whereDate('created_at', $this->date)
            ->count();

        $brandKitCoverage = BrandKit::where('organization_id', $user->organization_id)->count();

        return view('livewire.dashboards.index', [
            'date' => $this->date,
            'widgets' => $this->widgets,
            'orgKpis' => $orgKpis,
            'prodTotals' => [
                'hours' => (float) $prod->sum('hours_tracked'),
                'billable_ratio' => $prod->sum('hours_tracked') > 0 ? (($prod->sum('billable_hours') / $prod->sum('hours_tracked')) * 100) : 0,
                'tasks_completed' => (int) $prod->sum('tasks_completed'),
                'overdue_tasks' => (int) $prod->sum('overdue_tasks'),
            ],
            'competitive' => [
                'new_ads' => (int) $competitiveAds,
                'open_alerts' => (int) $competitiveAnoms,
            ],
            'counts' => [
                'clients' => $clients,
                'snapshots' => $snapshots,
            ],
            'playbooks' => [
                'runs_today' => $playbookRuns,
                'open_tasks' => $playbookTasksOpen,
            ],
            'seoAudit' => [
                'critical_high_issues_today' => $seoIssues,
            ],
            'branding' => [
                'brand_kits' => $brandKitCoverage,
                'clients' => $clients,
            ],
        ]);
    }
}

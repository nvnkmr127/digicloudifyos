<?php

namespace App\Livewire\Seo;

use App\Models\GoogleBusinessProfileDailyMetric;
use App\Models\GoogleBusinessProfileMonthlyKeyword;
use App\Models\SeoOpportunity;
use App\Models\SeoSiteAuditIssue;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Index extends Component
{
    public string $date;

    public function mount(): void
    {
        $this->date = now()->subDay()->toDateString();
    }

    public function render()
    {
        $user = Auth::user();
        if (! $user instanceof User) {
            abort(403);
        }

        $items = SeoOpportunity::where('organization_id', $user->organization_id)
            ->whereDate('opportunity_date', $this->date)
            ->with('client')
            ->orderByRaw("FIELD(severity, 'critical','high','medium','low')")
            ->orderByDesc('created_at')
            ->limit(200)
            ->get();

        $auditIssues = SeoSiteAuditIssue::where('organization_id', $user->organization_id)
            ->whereIn('severity', ['critical', 'high', 'medium'])
            ->whereDate('created_at', $this->date)
            ->with('audit', 'audit.client')
            ->orderByRaw("FIELD(severity, 'critical','high','medium','low')")
            ->limit(200)
            ->get();

        $gbp = GoogleBusinessProfileDailyMetric::where('organization_id', $user->organization_id)
            ->whereDate('metric_date', $this->date)
            ->get();

        $gbpTotals = [
            'website_clicks' => (int) $gbp->sum('website_clicks'),
            'call_clicks' => (int) $gbp->sum('call_clicks'),
            'directions_requests' => (int) $gbp->sum('directions_requests'),
            'impressions' => (int) ($gbp->sum('impressions_search_desktop') + $gbp->sum('impressions_search_mobile') + $gbp->sum('impressions_maps_desktop') + $gbp->sum('impressions_maps_mobile')),
        ];

        $latestMonth = GoogleBusinessProfileMonthlyKeyword::where('organization_id', $user->organization_id)
            ->orderByDesc('month_start')
            ->value('month_start');

        $keywords = collect();
        if ($latestMonth) {
            $keywords = GoogleBusinessProfileMonthlyKeyword::where('organization_id', $user->organization_id)
                ->whereDate('month_start', Carbon::parse($latestMonth)->toDateString())
                ->orderByDesc('impressions')
                ->limit(20)
                ->get();
        }

        return view('livewire.seo.index', [
            'items' => $items,
            'auditIssues' => $auditIssues,
            'gbpTotals' => $gbpTotals,
            'gbpKeywords' => $keywords,
            'gbpMonth' => $latestMonth ? Carbon::parse($latestMonth)->toDateString() : null,
        ]);
    }
}

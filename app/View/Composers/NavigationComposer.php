<?php

namespace App\View\Composers;

use App\Models\PerformanceAnomaly;
use App\Models\BriefingActionItem;
use App\Models\DailyBriefing;
use Illuminate\View\View;
use Illuminate\Support\Facades\Cache;

class NavigationComposer
{
    /**
     * Bind data to the view.
     */
    public function compose(View $view): void
    {
        if (!auth()->check()) return;

        $orgId = auth()->user()->organization_id;

        // Cache for 5 minutes per user/org
        $counts = Cache::remember("nav_counts_{$orgId}", 300, function () use ($orgId) {
            $urgentCount = BriefingActionItem::whereHas('briefing', function ($q) use ($orgId) {
                    $q->where('organization_id', $orgId)
                      ->where('briefing_date', today());
                })
                ->where('priority_level', 'urgent')
                ->where('is_completed', false)
                ->count();

            $criticalCount = PerformanceAnomaly::where('organization_id', $orgId)
                ->unresolved()
                ->where('severity', 'critical')
                ->count();

            return [
                'urgentCount' => $urgentCount,
                'criticalCount' => $criticalCount,
            ];
        });

        $view->with($counts);
    }
}

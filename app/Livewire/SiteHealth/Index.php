<?php

namespace App\Livewire\SiteHealth;

use App\Models\DomainExpiryCheck;
use App\Models\PageSpeedDailyMetric;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

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

        $isToday = $this->date === now()->toDateString();
        $hasLagWarning = $isToday;

        // Validate date format (B028)
        try {
            $metricDate = Carbon::parse($this->date)->toDateString();
        } catch (\Exception $e) {
            $metricDate = now()->subDay()->toDateString();
            $this->date = $metricDate;
            $hasLagWarning = false;
        }

        $pagespeed = PageSpeedDailyMetric::where('organization_id', $user->organization_id)
            ->whereDate('metric_date', $metricDate)
            ->with('client')
            ->orderBy('performance_mobile')
            ->paginate(20, ['*'], 'pagespeedPage');

        $domains = DomainExpiryCheck::where('organization_id', $user->organization_id)
            ->whereDate('check_date', $metricDate)
            ->with('client')
            ->orderBy('days_remaining')
            ->paginate(20, ['*'], 'domainPage');

        return view('livewire.site-health.index', [
            'pagespeed' => $pagespeed,
            'domains' => $domains,
            'hasLagWarning' => $hasLagWarning,
        ]);
    }
}

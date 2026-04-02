<?php

namespace App\Livewire\SiteHealth;

use App\Models\DomainExpiryCheck;
use App\Models\PageSpeedDailyMetric;
use App\Models\User;
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

        $pagespeed = PageSpeedDailyMetric::where('organization_id', $user->organization_id)
            ->whereDate('metric_date', $this->date)
            ->with('client')
            ->orderBy('performance_mobile')
            ->limit(100)
            ->get();

        $domains = DomainExpiryCheck::where('organization_id', $user->organization_id)
            ->whereDate('check_date', $this->date)
            ->with('client')
            ->orderBy('days_remaining')
            ->limit(100)
            ->get();

        return view('livewire.site-health.index', [
            'pagespeed' => $pagespeed,
            'domains' => $domains,
        ]);
    }
}

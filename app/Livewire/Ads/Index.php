<?php

namespace App\Livewire\Ads;

use App\Models\AdAccount;
use App\Models\AdInsight;
use Livewire\Component;

class Index extends Component
{
    public function render()
    {
        $organizationId = auth()->user()->organization_id;

        $accounts = AdAccount::where('organization_id', $organizationId)
            ->withCount(['campaigns', 'adInsights'])
            ->get();

        $totalSpend = AdInsight::where('organization_id', $organizationId)
            ->where('level', 'account')
            ->where('date', '>=', now()->subDays(30))
            ->sum('spend');

        $totalImpressions = AdInsight::where('organization_id', $organizationId)
            ->where('level', 'account')
            ->where('date', '>=', now()->subDays(30))
            ->sum('impressions');

        $latestLeads = \App\Models\FacebookLead::where('organization_id', $organizationId)
            ->with(['campaign', 'ad'])
            ->latest()
            ->take(5)
            ->get();

        return view('livewire.ads.index', [
            'accounts' => $accounts,
            'totalSpend' => $totalSpend,
            'totalImpressions' => $totalImpressions,
            'latestLeads' => $latestLeads,
        ])->layout('layouts.app');
    }
}

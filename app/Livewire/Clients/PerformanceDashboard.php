<?php

namespace App\Livewire\Clients;

use App\Models\Client;
use App\Models\FacebookLead;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class PerformanceDashboard extends Component
{
    public $dateRange = 30;

    public function render()
    {
        $organizationId = Auth::user()->organization_id;
        $startDate = now()->subDays($this->dateRange)->toDateString();

        $clients = Client::where('organization_id', $organizationId)
            ->with(['adAccounts.adInsights' => function ($query) use ($startDate) {
                $query->where('date', '>=', $startDate)->where('level', 'account');
            }])
            ->get()
            ->map(function ($client) use ($startDate) {
                $totalSpend = 0;
                $totalConversions = 0;
                $totalRevenue = 0;
                $adAccountIds = $client->adAccounts->pluck('id');

                foreach ($client->adAccounts as $account) {
                    $totalSpend += $account->adInsights->sum('spend');
                    $totalConversions += $account->adInsights->sum('conversions');
                    $totalRevenue += $account->adInsights->sum('revenue');
                }

                $leadsCount = FacebookLead::whereIn('ad_account_id', $adAccountIds)
                    ->where('created_at', '>=', $startDate)
                    ->count();

                return [
                    'id' => $client->id,
                    'name' => $client->name,
                    'spend' => $totalSpend,
                    'leads' => $leadsCount,
                    'cpl' => $leadsCount > 0 ? $totalSpend / $leadsCount : 0,
                    'roas' => $totalSpend > 0 ? $totalRevenue / $totalSpend : 0,
                    'conversions' => $totalConversions,
                ];
            })->sortByDesc('spend');

        return view('livewire.clients.performance-dashboard', [
            'clients' => $clients,
        ])->layout('layouts.app');
    }
}

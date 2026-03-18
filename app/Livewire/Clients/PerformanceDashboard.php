<?php

namespace App\Livewire\Clients;

use App\Models\Client;
use App\Models\AdInsight;
use App\Models\FacebookLead;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class PerformanceDashboard extends Component
{
    public $dateRange = 30;

    public function render()
    {
        $organizationId = Auth::user()->organization_id;
        $startDate = now()->subDays($this->dateRange)->toDateString();

        $clients = Client::where('organization_id', $organizationId)
            ->with(['adAccounts.adInsights' => function($query) use ($startDate) {
                $query->where('date', '>=', $startDate)->where('level', 'account');
            }])
            ->get()
            ->map(function ($client) use ($startDate) {
                $totalSpend = 0;
                $totalConversions = 0;
                $totalRoasSum = 0;
                $insightCount = 0;
                $adAccountIds = $client->adAccounts->pluck('id');

                foreach ($client->adAccounts as $account) {
                    $totalSpend += $account->adInsights->sum('spend');
                    $totalConversions += $account->adInsights->sum('conversions');
                    
                    $avgRoas = $account->adInsights->avg('roas');
                    if ($avgRoas !== null) {
                        $totalRoasSum += $avgRoas;
                        $insightCount++;
                    }
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
                    'roas' => $insightCount > 0 ? $totalRoasSum / $insightCount : 0,
                    'conversions' => $totalConversions,
                ];
            })->sortByDesc('spend');

        return view('livewire.clients.performance-dashboard', [
            'clients' => $clients,
        ])->layout('layouts.app');
    }
}

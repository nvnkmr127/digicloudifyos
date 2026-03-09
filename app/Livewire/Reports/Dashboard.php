<?php

namespace App\Livewire\Reports;

use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $organizationId = \Illuminate\Support\Facades\Auth::user()->organization_id;

        $campaignStats = \App\Models\Campaign::where('organization_id', $organizationId)
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->get();

        $leadStats = \App\Models\Lead::where('organization_id', $organizationId)
            ->selectRaw('source, count(*) as count')
            ->groupBy('source')
            ->get();

        $monthlyInvoices = \App\Models\Invoice::where('organization_id', $organizationId)
            ->selectRaw("strftime('%Y-%m', created_at) as month, sum(total_amount) as total")
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->limit(6)
            ->get();

        return view('livewire.reports.dashboard', [
            'campaignStats' => $campaignStats,
            'leadStats' => $leadStats,
            'monthlyInvoices' => $monthlyInvoices,
        ])->layout('layouts.app');
    }
}

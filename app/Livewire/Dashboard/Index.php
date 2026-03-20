<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;

class Index extends Component
{
    public function render()
    {
        $orgId = \Illuminate\Support\Facades\Auth::user()->organization_id;
        
        $stats = [
            'total_clients' => \App\Models\Client::where('organization_id', $orgId)->count(),
            'campaigns_count' => \App\Models\Campaign::where('organization_id', $orgId)->count(),
            'projects_count' => \App\Models\Project::where('organization_id', $orgId)->count(),
            'total_revenue' => \App\Models\Invoice::where('organization_id', $orgId)->where('status', 'paid')->sum('paid_amount'),
            'recent_documents' => \App\Models\Invoice::where('organization_id', $orgId)->with('client')->orderByDesc('created_at')->limit(5)->get(),
        ];

        return view('livewire.dashboard.index', $stats)->layout('layouts.app');
    }
}

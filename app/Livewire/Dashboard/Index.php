<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;

class Index extends Component
{
    public function render()
    {
        $stats = [
            'total_clients' => \App\Models\Client::count(),
            'campaigns_count' => \App\Models\Campaign::count(),
            'projects_count' => \App\Models\Project::count(),
            'total_revenue' => \App\Models\Invoice::where('status', 'paid')->sum('paid_amount'),
            'recent_documents' => \App\Models\Invoice::with('client')->orderByDesc('created_at')->limit(5)->get(),
        ];

        return view('livewire.dashboard.index', $stats)->layout('layouts.app');
    }
}

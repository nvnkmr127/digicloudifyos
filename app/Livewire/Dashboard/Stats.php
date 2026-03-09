<?php

namespace App\Livewire\Dashboard;

use App\Models\Alert;
use App\Models\Campaign;
use App\Models\Lead;
use App\Models\Task;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Stats extends Component
{
    public $stats = [];

    public function mount()
    {
        $organizationId = Auth::user()->organization_id;

        $this->stats = [
            'campaigns' => [
                'total' => Campaign::forOrganization($organizationId)->count(),
                'running' => Campaign::forOrganization($organizationId)->where('status', 'running')->count(),
                'completed' => Campaign::forOrganization($organizationId)->where('status', 'completed')->count(),
            ],
            'tasks' => [
                'total' => Task::forOrganization($organizationId)->count(),
                'pending' => Task::forOrganization($organizationId)->byStatus('pending')->count(),
                'in_progress' => Task::forOrganization($organizationId)->byStatus('in_progress')->count(),
                'overdue' => Task::forOrganization($organizationId)->overdue()->count(),
            ],
            'leads' => [
                'total' => Lead::forOrganization($organizationId)->count(),
                'new' => Lead::forOrganization($organizationId)->byStatus('New')->count(),
                'won' => Lead::forOrganization($organizationId)->byStatus('Won')->count(),
                'lost' => Lead::forOrganization($organizationId)->byStatus('Lost')->count(),
            ],
            'alerts' => [
                'total' => Alert::where('organization_id', $organizationId)->count(),
                'open' => Alert::where('organization_id', $organizationId)->open()->count(),
                'critical' => Alert::where('organization_id', $organizationId)->critical()->count(),
            ],
        ];
    }

    public function render()
    {
        return view('livewire.dashboard.stats');
    }
}

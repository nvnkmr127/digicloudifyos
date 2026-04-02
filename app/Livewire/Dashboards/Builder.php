<?php

namespace App\Livewire\Dashboards;

use App\Models\DashboardLayout;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Builder extends Component
{
    public string $name = 'My Dashboard';

    public array $selectedWidgets = [];

    protected array $available = [
        'org_kpis' => 'Org KPIs',
        'roi_summary' => 'ROI Summary',
        'workload' => 'Workload & Capacity',
        'productivity' => 'Productivity',
        'competitive' => 'Competitive Signals',
        'client_snapshots' => 'Client Snapshots',
        'playbooks' => 'Playbook Progress',
        'seo_audit' => 'SEO Site Audit',
        'brand_kit' => 'Brand Kit Coverage',
    ];

    public function mount(): void
    {
        $user = Auth::user();
        if (! $user instanceof User) {
            abort(403);
        }

        $layout = DashboardLayout::where('organization_id', $user->organization_id)
            ->where('user_id', $user->id)
            ->where('is_default', true)
            ->first();

        if ($layout) {
            $this->name = $layout->name;
            $this->selectedWidgets = collect($layout->widgets)->pluck('type')->filter()->values()->all();
        } else {
            $this->selectedWidgets = ['org_kpis', 'roi_summary', 'productivity'];
        }
    }

    public function save(): void
    {
        $user = Auth::user();
        if (! $user instanceof User) {
            abort(403);
        }

        $widgets = collect($this->selectedWidgets)
            ->filter(fn ($t) => is_string($t) && isset($this->available[$t]))
            ->values()
            ->map(fn ($t) => ['type' => $t])
            ->all();

        DashboardLayout::where('organization_id', $user->organization_id)
            ->where('user_id', $user->id)
            ->where('is_default', true)
            ->update(['is_default' => false]);

        DashboardLayout::updateOrCreate(
            [
                'organization_id' => $user->organization_id,
                'user_id' => $user->id,
                'is_default' => true,
            ],
            [
                'name' => $this->name,
                'widgets' => $widgets,
            ]
        );

        session()->flash('success', 'Dashboard saved.');
    }

    public function render()
    {
        return view('livewire.dashboards.builder', [
            'available' => $this->available,
        ]);
    }
}

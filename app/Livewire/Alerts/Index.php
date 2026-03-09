<?php

namespace App\Livewire\Alerts;

use Livewire\Component;

class Index extends Component
{
    public $alerts;

    public function mount()
    {
        $this->refreshAlerts();
    }

    public function refreshAlerts()
    {
        $this->alerts = \App\Models\Alert::where('organization_id', \Illuminate\Support\Facades\Auth::user()->organization_id)
            ->where('status', '!=', 'RESOLVED')
            ->orderBy('severity', 'desc')
            ->orderBy('triggered_at', 'desc')
            ->get();
    }

    public function acknowledge($id)
    {
        $alert = \App\Models\Alert::find($id);
        if ($alert && $alert->organization_id === \Illuminate\Support\Facades\Auth::user()->organization_id) {
            $alert->acknowledge();
            $this->refreshAlerts();
        }
    }

    public function resolve($id)
    {
        $alert = \App\Models\Alert::find($id);
        if ($alert && $alert->organization_id === \Illuminate\Support\Facades\Auth::user()->organization_id) {
            $alert->resolve();
            $this->refreshAlerts();
        }
    }

    public function render()
    {
        return view('livewire.alerts.index')->layout('layouts.app');
    }
}

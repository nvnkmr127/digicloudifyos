<?php

namespace App\Livewire\Alerts;

use App\Models\Alert;
use Illuminate\Support\Facades\Auth;
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
        $this->alerts = Alert::where('organization_id', Auth::user()->organization_id)
            ->where('status', '!=', 'RESOLVED')
            ->orderBy('severity', 'desc')
            ->orderBy('triggered_at', 'desc')
            ->get();
    }

    public function acknowledge($id)
    {
        $alert = Alert::find($id);
        if ($alert && $alert->organization_id === Auth::user()->organization_id) {
            $alert->acknowledge();
            $this->refreshAlerts();
        }
    }

    public function resolve($id)
    {
        $alert = Alert::find($id);
        if ($alert && $alert->organization_id === Auth::user()->organization_id) {
            $alert->resolve();
            $this->refreshAlerts();
        }
    }

    public function render()
    {
        return view('livewire.alerts.index')->layout('layouts.app');
    }
}

<?php

namespace App\Livewire\Alerts;

use App\Models\Alert;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Index extends Component
{
    public function acknowledge($id)
    {
        $alert = Alert::where('organization_id', Auth::user()->organization_id)->find($id);
        if ($alert) {
            $alert->acknowledge();
        }
    }

    public function resolve($id)
    {
        $alert = Alert::where('organization_id', Auth::user()->organization_id)->find($id);
        if ($alert) {
            $alert->resolve();
        }
    }

    public function render()
    {
        $alerts = Alert::where('organization_id', Auth::user()->organization_id)
            ->where('status', '!=', 'RESOLVED')
            ->orderBy('severity', 'desc')
            ->orderBy('triggered_at', 'desc')
            ->get();

        return view('livewire.alerts.index', [
            'alerts' => $alerts,
        ])->layout('layouts.app');
    }
}

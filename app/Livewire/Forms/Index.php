<?php

namespace App\Livewire\Forms;

use Livewire\Component;

class Index extends Component
{
    public function render()
    {
        $orgId = \Illuminate\Support\Facades\Auth::user()->organization_id;
        $forms = \App\Models\Form::where('organization_id', $orgId)
            ->withCount('submissions')
            ->latest()
            ->get();

        return view('livewire.forms.index', [
            'forms' => $forms,
        ]);
    }
}

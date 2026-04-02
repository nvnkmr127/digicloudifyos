<?php

namespace App\Livewire\Forms;

use App\Models\Form;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Index extends Component
{
    public function render()
    {
        $orgId = Auth::user()->organization_id;
        $forms = Form::where('organization_id', $orgId)
            ->withCount('submissions')
            ->latest()
            ->get();

        return view('livewire.forms.index', [
            'forms' => $forms,
        ]);
    }
}

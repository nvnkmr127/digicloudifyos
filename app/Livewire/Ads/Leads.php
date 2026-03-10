<?php

namespace App\Livewire\Ads;

use App\Models\FacebookLead;
use Livewire\Component;
use Livewire\WithPagination;

class Leads extends Component
{
    use WithPagination;

    public $search = '';
    public $formFilter = '';

    public function render()
    {
        $organizationId = auth()->user()->organization_id;

        $leads = FacebookLead::where('organization_id', $organizationId)
            ->when($this->search, function ($query) {
                $query->where('full_name', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%');
            })
            ->when($this->formFilter, function ($query) {
                $query->where('form_name', $this->formFilter);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $forms = FacebookLead::where('organization_id', $organizationId)
            ->whereNotNull('form_name')
            ->distinct()
            ->pluck('form_name');

        return view('livewire.ads.leads', [
            'leads' => $leads,
            'forms' => $forms,
        ])->layout('layouts.app');
    }
}

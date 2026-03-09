<?php

namespace App\Livewire\Clients;

use Livewire\Component;

class Index extends Component
{
    public $search = '';

    public function render()
    {
        $clients = \App\Models\Client::where('organization_id', \Illuminate\Support\Facades\Auth::user()->organization_id)
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('email', 'like', '%'.$this->search.'%');
            })
            ->withCount(['campaigns', 'leads'])
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.clients.index', [
            'clients' => $clients,
        ])->layout('layouts.app');
    }
}

<?php

namespace App\Livewire\Clients;

use Livewire\Component;

use App\Models\Client;
use Illuminate\Support\Facades\Auth;

class Create extends Component
{
    public $name = '';
    public $email = '';
    public $industry = '';
    public $external_ref = '';
    public $status = 'ACTIVE';

    protected $rules = [
        'name' => 'required|min:3',
        'email' => 'nullable|email',
        'industry' => 'nullable|string',
        'external_ref' => 'nullable|string',
        'status' => 'required|in:ACTIVE,INACTIVE,ARCHIVED',
    ];

    public function save()
    {
        $this->validate();

        Client::create([
            'organization_id' => Auth::user()->organization_id ?? null,
            'name' => $this->name,
            'email' => $this->email,
            'industry' => $this->industry,
            'external_ref' => $this->external_ref,
            'status' => $this->status,
        ]);

        session()->flash('success', 'Client created successfully.');

        return redirect()->route('clients.index');
    }

    public function render()
    {
        return view('livewire.clients.create');
    }
}

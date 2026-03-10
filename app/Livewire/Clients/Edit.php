<?php

namespace App\Livewire\Clients;

use Livewire\Component;

use App\Models\Client;
use Illuminate\Support\Facades\Auth;

class Edit extends Component
{
    public Client $client;
    public $name;
    public $email;
    public $industry;
    public $external_ref;
    public $status;

    protected $rules = [
        'name' => 'required|min:3',
        'email' => 'nullable|email',
        'industry' => 'nullable|string',
        'external_ref' => 'nullable|string',
        'status' => 'required|in:ACTIVE,INACTIVE,ARCHIVED',
    ];

    public function mount(Client $client)
    {
        $this->client = $client;
        $this->name = $client->name;
        $this->email = $client->email;
        $this->industry = $client->industry;
        $this->external_ref = $client->external_ref;
        $this->status = $client->status;
    }

    public function update()
    {
        $this->validate();

        $this->client->update([
            'name' => $this->name,
            'email' => $this->email,
            'industry' => $this->industry,
            'external_ref' => $this->external_ref,
            'status' => $this->status,
        ]);

        session()->flash('success', 'Client updated successfully.');

        return redirect()->route('clients.index');
    }

    public function render()
    {
        return view('livewire.clients.edit');
    }
}

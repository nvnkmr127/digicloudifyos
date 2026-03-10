<?php

namespace App\Livewire\Clients;

use Livewire\Component;

use App\Models\Client;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
    public $search = '';

    public function delete($id)
    {
        $client = Client::findOrFail($id);
        $client->delete();

        session()->flash('success', 'Client deleted successfully.');
    }

    public function render()
    {
        $clients = Client::where('organization_id', Auth::user()->organization_id)
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%')
                        ->orWhere('industry', 'like', '%' . $this->search . '%');
                });
            })
            ->withCount(['campaigns', 'leads'])
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.clients.index', [
            'clients' => $clients,
        ]);
    }
}

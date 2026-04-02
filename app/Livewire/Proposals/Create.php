<?php

namespace App\Livewire\Proposals;

use App\Models\Client;
use App\Models\Proposal;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Create extends Component
{
    public $clientId;

    public $title = '';

    public $description = '';

    public $totalAmount = 0;

    public $validUntil = '';

    public $content = [];

    protected $rules = [
        'clientId' => 'required|exists:clients,id',
        'title' => 'required|string|max:255',
        'totalAmount' => 'required|numeric|min:0',
        'validUntil' => 'nullable|date|after:today',
    ];

    public function mount()
    {
        $this->validUntil = now()->addDays(30)->format('Y-m-d');
    }

    public function save()
    {
        $this->validate();

        $proposalNumber = 'PROP-'.strtoupper(str()->random(8));

        Proposal::create([
            'organization_id' => Auth::user()->organization_id,
            'client_id' => $this->clientId,
            'proposal_number' => $proposalNumber,
            'title' => $this->title,
            'description' => $this->description,
            'total_amount' => $this->totalAmount,
            'status' => 'pending',
            'valid_until' => $this->validUntil,
            'content' => $this->content,
        ]);

        session()->flash('message', 'Proposal created successfully.');

        return redirect()->route('proposals.index');
    }

    public function render()
    {
        $clients = Client::where('organization_id', Auth::user()->organization_id)->get();

        return view('livewire.proposals.create', [
            'clients' => $clients,
        ])->layout('layouts.app');
    }
}

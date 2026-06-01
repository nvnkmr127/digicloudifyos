<?php

namespace App\Livewire\Proposals;

use App\Models\Client;
use App\Models\Proposal;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Create extends Component
{
    use AuthorizesRequests;

    public $clientId;

    public $title = '';

    public $description = '';

    public $totalAmount = 0;

    public $validUntil = '';

    public $content = [];

    protected function rules()
    {
        return [
            'clientId' => [
                'required',
                'uuid',
                Rule::exists('clients', 'id')->where('organization_id', Auth::user()->organization_id),
            ],
            'title' => 'required|string|max:255',
            'totalAmount' => 'required|numeric|min:0',
            'validUntil' => 'nullable|date|after:today',
        ];
    }

    public function mount()
    {
        $this->validUntil = now()->addDays(30)->format('Y-m-d');
    }

    public function save()
    {
        $this->authorize('create', Proposal::class);
        $this->validate();

        $proposalNumber = 'PROP-'.strtoupper(str()->random(8));

        Proposal::create([
            'organization_id' => Auth::user()->organization_id,
            'client_id' => $this->clientId,
            'proposal_number' => $proposalNumber,
            'title' => $this->title,
            'description' => $this->description,
            'total_amount' => $this->totalAmount,
            'status' => 'draft',
            'valid_until' => $this->validUntil,
            'content' => $this->content,
        ]);

        session()->flash('success', 'Proposal created successfully.');

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

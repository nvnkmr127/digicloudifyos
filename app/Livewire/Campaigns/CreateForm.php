<?php

namespace App\Livewire\Campaigns;

use Livewire\Component;

use App\Models\Campaign;
use App\Models\Client;
use App\Models\AdAccount;
use Illuminate\Support\Facades\Auth;

class CreateForm extends Component
{
    public $client_id = '';
    public $ad_account_id = '';
    public $name = '';
    public $objective = '';
    public $status = 'planned';
    public $start_date = '';
    public $end_date = '';
    public $daily_budget = 0;
    public $lifetime_budget = 0;

    protected $rules = [
        'client_id' => 'required|uuid|exists:clients,id',
        'ad_account_id' => 'nullable|uuid|exists:ad_accounts,id',
        'name' => 'required|min:3',
        'objective' => 'required|string',
        'status' => 'required|in:active,paused,completed,planned,archived',
        'start_date' => 'nullable|date',
        'end_date' => 'nullable|date',
        'daily_budget' => 'nullable|numeric|min:0',
        'lifetime_budget' => 'nullable|numeric|min:0',
    ];

    public function save()
    {
        $this->validate();

        Campaign::create([
            'organization_id' => Auth::user()->organization_id,
            'client_id' => $this->client_id,
            'ad_account_id' => $this->ad_account_id ?: null,
            'name' => $this->name,
            'objective' => $this->objective,
            'status' => $this->status,
            'start_date' => $this->start_date ?: null,
            'end_date' => $this->end_date ?: null,
            'daily_budget' => $this->daily_budget,
            'lifetime_budget' => $this->lifetime_budget,
        ]);

        session()->flash('success', 'Campaign created successfully.');

        return redirect()->route('campaigns.index');
    }

    public function render()
    {
        $clients = Client::where('organization_id', Auth::user()->organization_id)->get();
        // In reality, ad_accounts would be filtered by client too if they were linked differently, 
        // but for now let's just show all for the organization.
        $adAccounts = AdAccount::where('organization_id', Auth::user()->organization_id)->get();

        return view('livewire.campaigns.create-form', [
            'clients' => $clients,
            'adAccounts' => $adAccounts,
        ]);
    }
}

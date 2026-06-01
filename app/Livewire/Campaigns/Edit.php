<?php

namespace App\Livewire\Campaigns;

use App\Enums\CampaignObjective;
use App\Enums\CampaignStatus;
use App\Models\AdAccount;
use App\Models\Campaign;
use App\Models\Client;
use App\Services\CampaignService;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Edit extends Component
{
    use AuthorizesRequests;

    public Campaign $campaign;

    public $client_id;

    public $ad_account_id;

    public $name;

    public $objective;

    public $status;

    public $start_date;

    public $end_date;

    public $daily_budget;

    public $lifetime_budget;

    protected function rules(): array
    {
        return [
            'client_id' => 'required|uuid|exists:clients,id',
            'ad_account_id' => 'required|uuid|exists:ad_accounts,id',
            'name' => 'required|min:3',
            'objective' => ['required', 'string', Rule::in(CampaignObjective::values())],
            'status' => ['required', 'string', Rule::in(CampaignStatus::values())],
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'daily_budget' => 'nullable|numeric|min:0',
            'lifetime_budget' => 'nullable|numeric|min:0',
        ];
    }

    public function mount(Campaign $campaign)
    {
        $this->authorize('update', $campaign);
        $this->campaign = $campaign;
        $this->client_id = $campaign->client_id;
        $this->ad_account_id = $campaign->ad_account_id;
        $this->name = $campaign->name;
        $this->objective = $campaign->objective;
        $this->status = $campaign->status;
        $this->start_date = $campaign->start_date ? Carbon::parse($campaign->start_date)->format('Y-m-d') : '';
        $this->end_date = $campaign->end_date ? Carbon::parse($campaign->end_date)->format('Y-m-d') : '';
        $this->daily_budget = $campaign->daily_budget;
        $this->lifetime_budget = $campaign->lifetime_budget;
    }

    public function update()
    {
        $this->authorize('update', $this->campaign);
        $this->validate();

        $this->campaign->update([
            'client_id' => $this->client_id,
            'ad_account_id' => $this->ad_account_id,
            'name' => $this->name,
            'objective' => $this->objective,
            'status' => $this->status,
            'start_date' => $this->start_date ?: null,
            'end_date' => $this->end_date ?: null,
            'daily_budget' => $this->daily_budget,
            'lifetime_budget' => $this->lifetime_budget,
        ]);

        session()->flash('success', 'Campaign updated successfully.');

        return redirect()->route('campaigns.index');
    }

    public function delete(CampaignService $service)
    {
        $this->authorize('delete', $this->campaign);

        $service->delete($this->campaign);

        session()->flash('success', 'Campaign deleted successfully.');

        return redirect()->route('campaigns.index');
    }

    public function render()
    {
        $clients = Client::where('organization_id', Auth::user()->organization_id)->get();
        $adAccounts = AdAccount::where('organization_id', Auth::user()->organization_id)->get();

        return view('livewire.campaigns.edit', [
            'clients' => $clients,
            'adAccounts' => $adAccounts,
        ]);
    }
}

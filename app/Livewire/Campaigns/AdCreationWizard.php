<?php

namespace App\Livewire\Campaigns;

use App\Models\AdAccount;
use App\Models\Client;
use App\Services\MetaAdsService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;

class AdCreationWizard extends Component
{
    public $step = 1;

    // Step 1: Campaign
    public $client_id;

    public $ad_account_id;

    public $campaign_name;

    public $objective = 'OUTCOME_REACH';

    // Step 2: Ad Set
    public $ad_set_name;

    public $daily_budget = 10;

    public $locations = ['United States'];

    // Step 3: Ad
    public $ad_name;

    public $headline;

    public $body_text;

    public $creative_id; // For simplicity in this demo

    protected function rules()
    {
        $orgId = Auth::user()->organization_id;

        return [
            1 => [
                'client_id' => [
                    'required',
                    Rule::exists('clients', 'id')->where('organization_id', $orgId),
                ],
                'ad_account_id' => [
                    'required',
                    Rule::exists('ad_accounts', 'id')->where('organization_id', $orgId),
                ],
                'campaign_name' => 'required|min:3',
                'objective' => 'required',
            ],
            2 => [
                'ad_set_name' => 'required|min:3',
                'daily_budget' => 'required|numeric|min:1',
            ],
            3 => [
                'ad_name' => 'required|min:3',
                'headline' => 'required',
                'body_text' => 'required',
            ],
        ];
    }

    public function nextStep()
    {
        $this->validate($this->rules()[$this->step]);
        $this->step++;
    }

    public function previousStep()
    {
        $this->step--;
    }

    public function create()
    {
        $this->validate($this->rules()[3]);

        $adAccount = AdAccount::where('id', $this->ad_account_id)
            ->where('organization_id', Auth::user()->organization_id)
            ->firstOrFail();

        $service = match ($adAccount->platform) {
            'META_ADS' => new MetaAdsService,
            'GOOGLE_ADS' => new GoogleAdsService,
            'LINKEDIN_ADS' => new LinkedInAdsService,
            default => null,
        };

        if (! $service) {
            session()->flash('error', 'Unsupported platform for wizard creation.');

            return;
        }

        if ($adAccount->platform !== 'META_ADS') {
            session()->flash('error', 'Automated creation is currently only supported for Meta Ads. Please use the platform dashboard for '.str_replace('_', ' ', $adAccount->platform).'.');

            return;
        }

        $campaign = null;
        try {
            // 1. Create Campaign
            $campaign = $service->createCampaign($adAccount, [
                'client_id' => $this->client_id,
                'name' => $this->campaign_name,
                'objective' => $this->objective,
            ]);

            // 2. Create Ad Set
            $adSet = $service->createAdSet($campaign, [
                'name' => $this->ad_set_name,
                'daily_budget' => $this->daily_budget,
                'targeting' => [
                    'geo_locations' => [
                        'countries' => ['US'],
                    ],
                ],
            ]);

            // 3. Create Ad
            $service->createAd($adSet, [
                'name' => $this->ad_name,
                'creative_id' => $this->creative_id ?? '2385150000000',
            ]);

            return redirect()->route('campaigns.index')
                ->with('message', 'Campaign, Ad Set, and Ad created successfully on Meta!');

        } catch (\Exception $e) {
            // Atomicity Patch: Attempt to delete the partially created campaign on Meta if deployment fails
            if ($campaign && method_exists($service, 'deleteCampaign')) {
                try {
                    $service->deleteCampaign($campaign);
                } catch (\Exception $cleanupEx) {
                    \Log::error('Wizard cleanup failed: '.$cleanupEx->getMessage());
                }
            }

            session()->flash('error', 'Meta API Error: '.$e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.campaigns.ad-creation-wizard', [
            'clients' => Client::where('organization_id', Auth::user()->organization_id)->get(),
            'adAccounts' => AdAccount::where('organization_id', Auth::user()->organization_id)->where('platform', 'META_ADS')->get(),
        ])->layout('layouts.app');
    }
}

<?php

namespace App\Livewire\Clients;

use App\Models\Client;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class OnboardingWizard extends Component
{
    public Client $client;

    public int $currentStep = 1;

    public int $totalSteps = 5;

    public array $stepTitles = [
        1 => 'Company Profile',
        2 => 'Global Configuration',
        3 => 'Strategic Goals',
        4 => 'Compliance & Status',
        5 => 'Next Steps & Checklist',
    ];

    // Step 1: Business Profile
    public $industry = '';

    public $website_url = '';

    public $phone = '';

    public $business_description = '';

    // Step 2: Address & Config
    public $timezone = '';

    public $currency_code = '';

    public $address_line1 = '';

    public $address_line2 = '';

    public $city = '';

    public $state = '';

    public $postal_code = '';

    public $country_code = '';

    // Step 3: Strategy
    public $goalsText = '';

    public $targetAudienceText = '';

    public $competitorsText = '';

    public $primaryKpisText = '';

    // Step 4: Compliance & Status
    public $gdpr_consent = false;

    public $ccpa_opt_out = false;

    public $data_retention_days = null;

    public $privacy_contact_email = '';

    public $status = 'ACTIVE';

    public function mount(Client $client)
    {
        if ($client->organization_id !== Auth::user()->organization_id) {
            abort(403);
        }

        $this->client = $client;

        // Pre-fill from existing data if any
        $this->industry = $client->industry;
        $this->website_url = $client->website_url;
        $this->phone = $client->phone;
        $this->business_description = $client->business_description;

        $this->timezone = $client->timezone;
        $this->currency_code = $client->currency_code;
        $this->address_line1 = $client->address_line1;
        $this->address_line2 = $client->address_line2;
        $this->city = $client->city;
        $this->state = $client->state;
        $this->postal_code = $client->postal_code;
        $this->country_code = $client->country_code;

        $this->goalsText = implode("\n", $client->goals ?? []);
        $this->targetAudienceText = implode("\n", $client->target_audience ?? []);
        $this->competitorsText = implode("\n", $client->competitors ?? []);
        $this->primaryKpisText = implode("\n", $client->primary_kpis ?? []);

        $this->gdpr_consent = (bool) $client->gdpr_consent_at;
        $this->ccpa_opt_out = (bool) $client->ccpa_opt_out_at;
        $this->data_retention_days = $client->data_retention_days;
        $this->privacy_contact_email = $client->privacy_contact_email;
        $this->status = $client->status;
    }

    protected function stepRules(): array
    {
        return [
            1 => [
                'industry' => 'nullable|string',
                'website_url' => 'nullable|url',
                'phone' => 'nullable|string|max:50',
                'business_description' => 'nullable|string',
            ],
            2 => [
                'timezone' => 'nullable|timezone',
                'currency_code' => 'nullable|string|size:3',
                'address_line1' => 'nullable|string|max:255',
                'address_line2' => 'nullable|string|max:255',
                'city' => 'nullable|string|max:255',
                'state' => 'nullable|string|max:255',
                'postal_code' => 'nullable|string|max:50',
                'country_code' => 'nullable|string|size:2',
            ],
            3 => [
                'goalsText' => 'nullable|string',
                'targetAudienceText' => 'nullable|string',
                'competitorsText' => 'nullable|string',
                'primaryKpisText' => 'nullable|string',
            ],
            4 => [
                'gdpr_consent' => 'boolean',
                'ccpa_opt_out' => 'boolean',
                'data_retention_days' => 'nullable|integer|min:1|max:3650',
                'privacy_contact_email' => 'nullable|email',
                'status' => 'required|in:ACTIVE,INACTIVE,ARCHIVED',
            ],
            5 => [],
        ];
    }

    public function nextStep()
    {
        $this->validate($this->stepRules()[$this->currentStep]);

        $this->saveCurrentStepData();

        if ($this->currentStep < $this->totalSteps) {
            $this->currentStep++;
        } else {
            return redirect()->route('clients.edit', $this->client->id)
                ->with('success', 'Onboarding completed successfully.');
        }
    }

    public function previousStep()
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }

    protected function saveCurrentStepData()
    {
        $data = [];

        if ($this->currentStep === 1) {
            $data = [
                'industry' => $this->industry,
                'website_url' => $this->website_url,
                'phone' => $this->phone,
                'business_description' => $this->business_description,
            ];
        } elseif ($this->currentStep === 2) {
            $data = [
                'timezone' => $this->timezone,
                'currency_code' => $this->currency_code ? strtoupper($this->currency_code) : null,
                'address_line1' => $this->address_line1,
                'address_line2' => $this->address_line2,
                'city' => $this->city,
                'state' => $this->state,
                'postal_code' => $this->postal_code,
                'country_code' => $this->country_code ? strtoupper($this->country_code) : null,
            ];
        } elseif ($this->currentStep === 3) {
            $data = [
                'goals' => $this->textToList($this->goalsText),
                'target_audience' => $this->textToList($this->targetAudienceText),
                'competitors' => $this->textToList($this->competitorsText),
                'primary_kpis' => $this->textToList($this->primaryKpisText),
            ];
        } elseif ($this->currentStep === 4) {
            $data = [
                'gdpr_consent_at' => $this->gdpr_consent ? ($this->client->gdpr_consent_at ?? now()) : null,
                'ccpa_opt_out_at' => $this->ccpa_opt_out ? ($this->client->ccpa_opt_out_at ?? now()) : null,
                'data_retention_days' => $this->data_retention_days,
                'privacy_contact_email' => $this->privacy_contact_email,
                'status' => $this->status,
            ];
        }

        $this->client->update($data);
    }

    protected function textToList(?string $text): array
    {
        $text = trim((string) $text);
        if ($text === '') {
            return [];
        }

        return collect(preg_split("/\r\n|\n|\r/", $text))
            ->map(fn ($v) => trim((string) $v))
            ->filter()
            ->values()
            ->all();
    }

    public function render()
    {
        return view('livewire.clients.onboarding-wizard');
    }
}

<?php

namespace App\Livewire\Clients;

use App\Models\Client;
use App\Models\ClientServicePackage;
use App\Models\ServicePackage;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Edit extends Component
{
    public Client $client;

    public $name;

    public $email;

    public $website_url;

    public $phone;

    public $industry;

    public $timezone;

    public $currency_code;

    public $address_line1;

    public $address_line2;

    public $city;

    public $state;

    public $postal_code;

    public $country_code;

    public $business_description;

    public $goalsText;

    public $targetAudienceText;

    public $competitorsText;

    public $primaryKpisText;

    public $gdpr_consent;

    public $ccpa_opt_out;

    public $data_retention_days;

    public $privacy_contact_email;

    public $external_ref;

    public $status;

    public array $selectedServicePackages = [];

    protected $rules = [
        'name' => 'required|min:3',
        'email' => 'nullable|email',
        'website_url' => 'nullable|url',
        'phone' => 'nullable|string|max:50',
        'industry' => 'nullable|string',
        'timezone' => 'nullable|timezone',
        'currency_code' => 'nullable|string|size:3',
        'address_line1' => 'nullable|string|max:255',
        'address_line2' => 'nullable|string|max:255',
        'city' => 'nullable|string|max:255',
        'state' => 'nullable|string|max:255',
        'postal_code' => 'nullable|string|max:50',
        'country_code' => 'nullable|string|size:2',
        'business_description' => 'nullable|string',
        'goalsText' => 'nullable|string',
        'targetAudienceText' => 'nullable|string',
        'competitorsText' => 'nullable|string',
        'primaryKpisText' => 'nullable|string',
        'gdpr_consent' => 'boolean',
        'ccpa_opt_out' => 'boolean',
        'data_retention_days' => 'nullable|integer|min:1|max:3650',
        'privacy_contact_email' => 'nullable|email',
        'external_ref' => 'nullable|string',
        'status' => 'required|in:ACTIVE,INACTIVE,ARCHIVED',
    ];

    protected function listToText($value): string
    {
        $items = is_array($value) ? $value : [];
        $items = collect($items)->map(fn ($v) => trim((string) $v))->filter()->values()->all();

        return implode("\n", $items);
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

    public function mount(Client $client)
    {
        $user = Auth::user();
        if (! $user instanceof User || ! $user->isAdmin()) {
            abort(403);
        }

        if ($client->organization_id !== $user->organization_id) {
            abort(403);
        }

        $this->client = $client;
        $this->name = $client->name;
        $this->email = $client->email;
        $this->website_url = $client->website_url;
        $this->phone = $client->phone;
        $this->industry = $client->industry;
        $this->timezone = $client->timezone;
        $this->currency_code = $client->currency_code;
        $this->address_line1 = $client->address_line1;
        $this->address_line2 = $client->address_line2;
        $this->city = $client->city;
        $this->state = $client->state;
        $this->postal_code = $client->postal_code;
        $this->country_code = $client->country_code;
        $this->business_description = $client->business_description;
        $this->goalsText = $this->listToText($client->goals);
        $this->targetAudienceText = $this->listToText($client->target_audience);
        $this->competitorsText = $this->listToText($client->competitors);
        $this->primaryKpisText = $this->listToText($client->primary_kpis);
        $this->gdpr_consent = (bool) $client->gdpr_consent_at;
        $this->ccpa_opt_out = (bool) $client->ccpa_opt_out_at;
        $this->data_retention_days = $client->data_retention_days;
        $this->privacy_contact_email = $client->privacy_contact_email;
        $this->external_ref = $client->external_ref;
        $this->status = $client->status;

        $this->selectedServicePackages = ClientServicePackage::where('organization_id', $user->organization_id)
            ->where('client_id', $client->id)
            ->where('is_active', true)
            ->pluck('service_package_id')
            ->values()
            ->all();
    }

    public function update()
    {
        $user = Auth::user();
        if (! $user instanceof User || ! $user->isAdmin()) {
            abort(403);
        }

        if ($this->client->organization_id !== $user->organization_id) {
            abort(403);
        }

        $this->validate();

        $this->client->update([
            'name' => $this->name,
            'email' => $this->email,
            'website_url' => $this->website_url ?: null,
            'phone' => $this->phone ?: null,
            'industry' => $this->industry,
            'timezone' => $this->timezone ?: null,
            'currency_code' => $this->currency_code ? strtoupper($this->currency_code) : null,
            'address_line1' => $this->address_line1 ?: null,
            'address_line2' => $this->address_line2 ?: null,
            'city' => $this->city ?: null,
            'state' => $this->state ?: null,
            'postal_code' => $this->postal_code ?: null,
            'country_code' => $this->country_code ? strtoupper($this->country_code) : null,
            'business_description' => $this->business_description ?: null,
            'goals' => $this->textToList($this->goalsText),
            'target_audience' => $this->textToList($this->targetAudienceText),
            'competitors' => $this->textToList($this->competitorsText),
            'primary_kpis' => $this->textToList($this->primaryKpisText),
            'gdpr_consent_at' => $this->gdpr_consent ? ($this->client->gdpr_consent_at ?? now()) : null,
            'ccpa_opt_out_at' => $this->ccpa_opt_out ? ($this->client->ccpa_opt_out_at ?? now()) : null,
            'data_retention_days' => $this->data_retention_days,
            'privacy_contact_email' => $this->privacy_contact_email ?: null,
            'external_ref' => $this->external_ref,
            'status' => $this->status,
        ]);

        $selected = collect($this->selectedServicePackages)->map(fn ($v) => (string) $v)->filter()->values()->all();

        ClientServicePackage::where('organization_id', $user->organization_id)
            ->where('client_id', $this->client->id)
            ->whereNotIn('service_package_id', $selected)
            ->update(['is_active' => false]);

        foreach ($selected as $pkgId) {
            ClientServicePackage::updateOrCreate(
                [
                    'organization_id' => $user->organization_id,
                    'client_id' => $this->client->id,
                    'service_package_id' => $pkgId,
                ],
                [
                    'is_active' => true,
                    'started_at' => now(),
                ]
            );
        }

        session()->flash('success', 'Client updated successfully.');

        return redirect()->route('clients.edit', $this->client->id);
    }

    public function render()
    {
        $user = Auth::user();
        if (! $user instanceof User || ! $user->isAdmin()) {
            abort(403);
        }

        $packages = ServicePackage::where('organization_id', $user->organization_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'industry', 'cadence']);

        return view('livewire.clients.edit', [
            'packages' => $packages,
        ]);
    }
}

<?php

namespace App\Livewire\Clients;

use App\Models\Client;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Create extends Component
{
    use AuthorizesRequests;

    public $name = '';

    public $email = '';

    public $website_url = '';

    public $phone = '';

    public $industry = '';

    public $timezone = '';

    public $currency_code = '';

    public $address_line1 = '';

    public $address_line2 = '';

    public $city = '';

    public $state = '';

    public $postal_code = '';

    public $country_code = '';

    public $business_description = '';

    public $goalsText = '';

    public $targetAudienceText = '';

    public $competitorsText = '';

    public $primaryKpisText = '';

    public $gdpr_consent = false;

    public $ccpa_opt_out = false;

    public $data_retention_days = null;

    public $privacy_contact_email = '';

    public $external_ref = '';

    public $status = 'ACTIVE';

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

    public function save()
    {
        \Log::debug('Attempting to create client', [
            'user_id' => Auth::id(),
            'organization_id' => Auth::user()->organization_id ?? null,
            'input' => $this->all(),
        ]);

        $this->authorize('create', Client::class);

        try {
            $this->validate();
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::debug('Validation failed during client creation', [
                'errors' => $e->errors(),
            ]);
            throw $e;
        }

        try {
            $client = Client::create([
                'organization_id' => Auth::user()->organization_id ?? null,
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
                'gdpr_consent_at' => $this->gdpr_consent ? now() : null,
                'ccpa_opt_out_at' => $this->ccpa_opt_out ? now() : null,
                'data_retention_days' => $this->data_retention_days,
                'privacy_contact_email' => $this->privacy_contact_email ?: null,
                'external_ref' => $this->external_ref ?: null,
                'status' => $this->status,
            ]);

            \Log::debug('Client created successfully', [
                'client_id' => $client->id,
                'organization_id' => $client->organization_id,
            ]);

            session()->flash('success', 'Client created successfully.');

            return redirect()->route('clients.edit', $client->id);
        } catch (\Exception $e) {
            \Log::error('Failed to create client', [
                'error_type' => get_class($e),
                'message' => $e->getMessage(),
                'input_data' => $this->all(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            session()->flash('error', 'Failed to create client: ' . $e->getMessage());
            return null;
        }
    }

    public function render()
    {
        $this->authorize('create', Client::class);

        return view('livewire.clients.create');
    }
}

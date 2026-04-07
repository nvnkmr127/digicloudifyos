<?php

namespace App\Http\Requests;

use App\Enums\CampaignObjective;
use App\Enums\CampaignStatus;
use App\Models\Campaign;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class StoreCampaignRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Campaign::class);
    }

    public function rules(): array
    {
        return [
            'client_id' => ['required', 'uuid', Rule::exists('clients', 'id')->where(function ($query) {
                $query->where('organization_id', $this->user()->organization_id);
            })],
            'ad_account_id' => ['required', 'uuid', Rule::exists('ad_accounts', 'id')],
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'external_campaign_id' => ['nullable', 'string', 'max:255'],
            'objective' => ['required', 'string', Rule::in(CampaignObjective::values())],
            'status' => ['required', 'string', Rule::in([
                ...CampaignStatus::values(),
            ])],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['nullable', 'date', 'after:start_date'],
            'daily_budget' => ['nullable', 'numeric', 'min:1', 'max:1000000'],
            'lifetime_budget' => ['nullable', 'numeric', 'min:1', 'max:10000000'],
        ];
    }

    public function messages(): array
    {
        return [
            'client_id.required' => 'Please select a client for this campaign.',
            'client_id.exists' => 'The selected client does not exist in your organization.',
            'ad_account_id.required' => 'Please select an ad account.',
            'name.required' => 'Campaign name is required.',
            'name.min' => 'Campaign name must be at least 3 characters.',
            'objective.required' => 'Please select a campaign objective.',
            'objective.in' => 'Invalid campaign objective selected.',
            'status.in' => 'Invalid campaign status.',
            'start_date.required' => 'Start date is required.',
            'start_date.after_or_equal' => 'Start date cannot be in the past.',
            'end_date.after' => 'End date must be after the start date.',
            'daily_budget.min' => 'Daily budget must be at least $1.',
            'lifetime_budget.min' => 'Lifetime budget must be at least $1.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'organization_id' => $this->user()->organization_id,
        ]);
    }

    public function validated($key = null, $default = null)
    {
        $validated = parent::validated($key, $default);

        if (! isset($validated['daily_budget']) && ! isset($validated['lifetime_budget'])) {
            throw ValidationException::withMessages([
                'budget' => ['Either daily budget or lifetime budget must be set.'],
            ]);
        }

        return $validated;
    }
}

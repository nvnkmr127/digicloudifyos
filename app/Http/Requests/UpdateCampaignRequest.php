<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCampaignRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('campaign'));
    }

    public function rules(): array
    {
        return [
            'client_id' => ['sometimes', 'uuid', Rule::exists('clients', 'id')->where(function ($query) {
                $query->where('organization_id', $this->user()->organization_id);
            })],
            'ad_account_id' => ['sometimes', 'uuid', Rule::exists('ad_accounts', 'id')],
            'name' => ['sometimes', 'string', 'min:3', 'max:255'],
            'external_campaign_id' => ['nullable', 'string', 'max:255'],
            'objective' => ['sometimes', 'string', Rule::in([
                'AWARENESS', 'TRAFFIC', 'ENGAGEMENT', 'LEADS', 'SALES', 'APP_PROMOTION',
            ])],
            'status' => ['sometimes', 'string', Rule::in([
                'planning', 'creative_requested', 'ready', 'running', 'optimizing', 'completed',
            ])],
            'start_date' => ['sometimes', 'date'],
            'end_date' => ['nullable', 'date', 'after:start_date'],
            'daily_budget' => ['nullable', 'numeric', 'min:1', 'max:1000000'],
            'lifetime_budget' => ['nullable', 'numeric', 'min:1', 'max:10000000'],
        ];
    }

    public function messages(): array
    {
        return [
            'client_id.exists' => 'The selected client does not exist in your organization.',
            'name.min' => 'Campaign name must be at least 3 characters.',
            'objective.in' => 'Invalid campaign objective selected.',
            'status.in' => 'Invalid campaign status.',
            'end_date.after' => 'End date must be after the start date.',
            'daily_budget.min' => 'Daily budget must be at least $1.',
            'lifetime_budget.min' => 'Lifetime budget must be at least $1.',
        ];
    }
}

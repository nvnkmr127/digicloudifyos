<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SocialListeningIngestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'organization_id' => ['required', 'uuid'],
            'client_id' => ['required', 'uuid'],
            'source_type' => ['nullable', 'string', 'max:50'],
            'events' => ['required', 'array'],
            'events.*.external_id' => ['required', 'string', 'max:255'],
            'events.*.event_date' => ['nullable', 'date'],
            'events.*.client_competitor_id' => ['nullable', 'uuid'],
            'events.*.title' => ['nullable', 'string', 'max:255'],
            'events.*.url' => ['nullable', 'string', 'max:2048'],
            'events.*.content' => ['nullable', 'string'],
            'events.*.author' => ['nullable', 'string', 'max:255'],
            'events.*.published_at' => ['nullable', 'date'],
        ];
    }
}

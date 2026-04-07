<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AnalyticsDashboardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'period' => ['sometimes', 'string', Rule::in([
                '7days', '30days', '90days', 'thisMonth', 'lastMonth', 'thisYear',
            ])],
        ];
    }
}


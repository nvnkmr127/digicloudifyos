<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WorkloadAnalysisRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'period' => ['sometimes', 'string', Rule::in([
                'current_week', 'next_week', 'current_month', 'next_month', 'current_quarter',
            ])],
        ];
    }
}


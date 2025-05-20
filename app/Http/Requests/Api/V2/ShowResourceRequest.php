<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ShowResourceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Add auth logic here if needed
    }

    public function rules(): array
    {
        return [
            'header:token' => ['required', 'string'],
            'header:datasetId' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'header:token.required' => 'The token header is required.',
            'header:datasetId.required' => 'The datasetId header is required.',
        ];
    }
}

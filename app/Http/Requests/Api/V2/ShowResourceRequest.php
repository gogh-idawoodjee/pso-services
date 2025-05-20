<?php

namespace App\Http\Requests\Api\V2;

use Illuminate\Foundation\Http\FormRequest;

class ShowResourceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // or add your auth logic here
    }

    // Make headers available for validation
    public function validationData()
    {
        // Pull headers you want to validate and normalize keys as needed
        return [
            'datasetId' => $this->header('datasetId'),
            'baseUrl' => $this->header('baseUrl'),
            'accountId' => $this->header('accountId'),
            'username' => $this->header('username'),
            'password' => $this->header('password'),
        ];
    }

    public function rules(): array
    {
        return [
            'datasetId' => ['required', 'string'],
            'baseUrl' => ['required', 'url'],
            'accountId' => ['required', 'string'],
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'datasetId.required' => 'The datasetId header is required.',
            'baseUrl.required' => 'The baseUrl header is required.',
            'accountId.required' => 'The accountId header is required.',
            'username.required' => 'The username header is required.',
            'password.required' => 'The password header is required.',
        ];
    }
}

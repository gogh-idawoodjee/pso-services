<?php

namespace App\Http\Requests\Api\V2;

use App\Rules\DisallowProdUrl;
use App\Traits\V2\ValidatesTokenOrCredentials;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class BaseGetFormRequest extends FormRequest
{
    use ValidatesTokenOrCredentials;

    public function authorize(): bool
    {
        return true;
    }

    public function validationData(): array
    {
        return [
            'datasetId' => $this->header('datasetId'),
            'baseUrl' => $this->header('baseUrl'),
            'accountId' => $this->header('accountId'),
            'username' => $this->header('username'),
            'password' => $this->header('password'),
            'token' => $this->header('token'),
        ];
    }

    public function commonRules(): array
    {
        return [
            'datasetId' => ['required', 'string'],
            'baseUrl' => ['required', 'url', new DisallowProdUrl],
            'accountId' => ['required', 'string'],
            'username' => ['string', 'nullable'],
            'password' => ['string', 'nullable'],
            'token' => ['string', 'nullable'],
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

    public function withValidator(Validator $validator): void
    {
        $this->requireTokenOrCredentials($validator, fn () => $this->validationData());
    }
}

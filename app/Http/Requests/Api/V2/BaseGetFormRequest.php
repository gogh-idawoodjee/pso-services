<?php

namespace App\Http\Requests\Api\V2;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class BaseGetFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // or add your auth logic here
    }

    // Make headers available for validation
    public function validationData(): array
    {
        // Pull headers you want to validate and normalize keys as needed
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
            'baseUrl' => ['required', 'url'],
            'accountId' => ['required', 'string'],
            'username' => ['string', 'nullable'],
            'password' => ['string', 'nullable'],
            'token' => ['string', 'nullable']
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
        $validator->after(function ($validator) {
            $data = $this->validationData();

            $token = trim($data['token'] ?? '');
            $user = trim($data['username'] ?? '');
            $pass = trim($data['password'] ?? '');

            $hasToken = $token !== '';
            $hasUser = $user !== '';
            $hasPass = $pass !== '';

            if (!$hasToken) {
                if (!$hasUser && !$hasPass) {
                    $validator->errors()->add('authentication', 'Either a token or both username and password must be provided.');
                } elseif (!$hasUser) {
                    $validator->errors()->add('username', 'Username is required if token is not provided.');
                } elseif (!$hasPass) {
                    $validator->errors()->add('password', 'Password is required if token is not provided.');
                }
            }
        });
    }

}

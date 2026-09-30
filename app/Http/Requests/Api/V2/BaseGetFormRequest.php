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
        // These are read from request headers, not the query string, so they are
        // hidden from Scramble's query parameters and documented as headers on the
        // actions by App\Http\OpenApi\PsoCommonParameters.
        return [
            /**
             * @ignoreParam
             */
            'datasetId' => ['required', 'string'],

            /**
             * @ignoreParam
             */
            'baseUrl' => ['required', 'url', new DisallowProdUrl],

            /**
             * @ignoreParam
             */
            'accountId' => ['required', 'string'],

            /**
             * @ignoreParam
             */
            'username' => ['string', 'nullable'],

            /**
             * @ignoreParam
             */
            'password' => ['string', 'nullable'],

            /**
             * @ignoreParam
             */
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

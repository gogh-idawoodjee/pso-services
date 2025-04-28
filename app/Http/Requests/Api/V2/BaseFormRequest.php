<?php

namespace App\Http\Requests\Api\V2;

use App\Rules\DisallowProdUrl;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class BaseFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function commonRules(): array
    {
        return [
            /**
             * Whether to send the request to PSO (true/false).
             * @var boolean
             * @example true
             */
            'environment.sendToPso' => [
                'boolean',
            ],

            /**
             * The base URL for the PSO environment.
             * Required if sendToPso is true.
             * @var string
             * @example "https://enercare-pso-tst.ifs.cloud"
             */
            'environment.baseUrl' => [
                'required_if:environment.sendToPso,true',
                'url',
                new DisallowProdUrl,
            ],

            /**
             * The authentication token (if already retrieved).
             * @var string|null
             * @example "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
             */
            'environment.token' => [
                'nullable',
                'string',
            ],

            /**
             * The username for PSO authentication (optional if using token).
             * @var string|null
             * @example "john.doe"
             */
            'environment.username' => [
                'nullable',
                'string',
                'required_with:environment.password',
            ],

            /**
             * The password for PSO authentication (optional if using token).
             * @var string|null
             * @example "P@ssw0rd!"
             */
            'environment.password' => [
                'nullable',
                'string',
                'required_with:environment.username',
            ],

            /**
             * The dataset ID to use in PSO.
             * Required if sendToPso is true.
             * @var string
             * @example "dataset_12345"
             */
            'environment.datasetId' => [
                'required_if:environment.sendToPso,true',
                'string',
            ],

            /**
             * The account ID for PSO.
             * Required if sendToPso is true.
             * @var string
             * @example "account_001"
             */
            'environment.accountId' => [
                'required_if:environment.sendToPso,true',
                'string',
            ],
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param Validator $validator
     * @return void
     */
    public function withValidator($validator): void
    {
        $validator->sometimes('environment.token', 'required', static function ($input) {
            return data_get($input, 'environment.sendToPso') === true &&
                (empty(data_get($input, 'environment.username')) || empty(data_get($input, 'environment.password')));
        });

        $validator->sometimes(['environment.username', 'environment.password'], 'required', static function ($input) {
            return data_get($input, 'environment.sendToPso') === true &&
                empty(data_get($input, 'environment.token'));
        });
    }
}

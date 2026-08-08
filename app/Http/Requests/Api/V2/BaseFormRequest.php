<?php

namespace App\Http\Requests\Api\V2;

use App\Rules\DisallowProdUrl;
use App\Traits\V2\ValidatesTokenOrCredentials;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class BaseFormRequest extends FormRequest
{
    use ValidatesTokenOrCredentials;

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
            ],

            /**
             * The password for PSO authentication (optional if using token).
             * @var string|null
             * @example "P@ssw0rd!"
             */
            'environment.password' => [
                'nullable',
                'string',
            ],

            /**
             * The dataset ID to use in PSO.
             * Required if sendToPso is true.
             * @var string
             * @example "dataset_12345"
             */
            'environment.datasetId' => [
                'required',
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

            /**
             * The PSO JSON format version (1 or 2).
             * Version 1: dsScheduleData wrapper with xmlns (pre-6.15 format).
             * Version 2: ScheduleData wrapper, proper JSON types, no nulls (6.15+).
             * @var int
             * @example 1
             */
            'environment.psoApiVersion' => [
                'integer',
                'in:1,2',
            ],
        ];
    }

    /**
     * Shared Scribe example data for the environment.* body parameters, reused
     * by bodyParameters() implementations across V2 FormRequests so the same
     * descriptions/examples don't need to be repeated in every subclass.
     */
    protected function commonBodyParameters(): array
    {
        return [
            'environment.sendToPso' => [
                'description' => 'Whether to send the built payload to PSO. If false (or omitted), the payload is returned without being sent — a dry run.',
                'example' => false,
            ],
            'environment.baseUrl' => [
                'description' => 'The base URL for the PSO environment. Required if sendToPso is true.',
                'example' => 'https://mycompany-pso-tst.ifs.cloud',
            ],
            'environment.token' => [
                'description' => 'An existing PSO authentication token, if already retrieved. Alternative to username/password.',
                'example' => null,
            ],
            'environment.username' => [
                'description' => 'The username for PSO authentication. Alternative to token.',
                'example' => null,
            ],
            'environment.password' => [
                'description' => 'The password for PSO authentication. Alternative to token.',
                'example' => null,
            ],
            'environment.datasetId' => [
                'description' => 'The dataset ID to use in PSO.',
                'example' => 'dataset_12345',
            ],
            'environment.accountId' => [
                'description' => 'The account ID for PSO. Required if sendToPso is true.',
                'example' => 'account_001',
            ],
            'environment.psoApiVersion' => [
                'description' => 'The PSO JSON format version: 1 for the pre-6.15 dsScheduleData format, 2 for the 6.15+ ScheduleData format.',
                'example' => 1,
            ],
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param Validator $validator
     * @return void
     */
    public function withValidator(Validator $validator): void
    {
        // Only required when sendToPso is true — unlike BaseGetFormRequest, whose
        // header-based auth is always required.
        $this->requireTokenOrCredentials(
            $validator,
            fn () => [
                'token' => $this->input('environment.token'),
                'username' => $this->input('environment.username'),
                'password' => $this->input('environment.password'),
            ],
            fn () => $this->input('environment.sendToPso') === true,
        );
    }
}

<?php

namespace App\Http\Requests\Api\V2;

use App\Rules\DisallowProdUrl;
use Illuminate\Validation\Validator;

/**
 * Unlike every other V2 request, auth fields here are required unconditionally
 * (not gated by environment.sendToPso) — cleanup always has to read the live
 * schedule from PSO to decide what's expired, and there's no preview mode:
 * whatever is found expired gets deleted.
 */
class CleanupRequest extends BaseFormRequest
{
    public function rules(): array
    {
        $commonRules = $this->commonRules();

        $commonRules['environment.baseUrl'] = ['required', 'url', new DisallowProdUrl];
        $commonRules['environment.accountId'] = ['required', 'string'];

        $additionalRules = [
            /**
             * Activities whose allocation ended at or before this datetime are
             * considered expired. Defaults to the start of today.
             *
             * @var string|null
             *
             * @example "2026-08-01T00:00:00"
             */
            'data.cutoffDatetime' => ['nullable', 'date'],
        ];

        return array_merge($commonRules, $additionalRules);
    }

    public function withValidator(Validator $validator): void
    {
        $this->requireTokenOrCredentials($validator, fn () => [
            'token' => $this->input('environment.token'),
            'username' => $this->input('environment.username'),
            'password' => $this->input('environment.password'),
        ]);
    }
}

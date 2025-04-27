<?php

namespace App\Http\Requests\Api\V2;

use App\Rules\DisallowProdUrl;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

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
            'environment.baseUrl' => [
                'required_if:environment.sendToPso,true',
                'url',
                new DisallowProdUrl,
            ],
            'environment.token' => [
                'nullable',
                'string',
            ],
            'environment.datasetId' => [
                'required_if:environment.sendToPso,true',
                'string',
            ],
            'environment.accountId' => [
                'required_if:environment.sendToPso,true',
                'string',
            ],
            'environment.username' => [
                'nullable',
                'string',
            ],
            'environment.password' => [
                'nullable',
                'string',
            ],
            'environment.sendToPso' => [
                'boolean',
            ],
        ];
    }




}

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
            'environment.base_url' => [
                'required',
                'url',
                new DisallowProdUrl
            ],
            'environment.token' => 'nullable|string', // Make token nullable, but if present, it's required
            'environment.dataset_id' => 'required|string',
            'environment.account_id' => 'required|string',
            'environment.username' => ['required_without_all:environment.token', 'nullable'], // username is required if token is not provided
            'environment.password' => ['required_without_all:environment.token', 'nullable'], // password is required if token is not provided
        ];
    }
}

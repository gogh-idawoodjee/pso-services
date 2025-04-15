<?php

namespace App\Http\Requests\Api\V2;

use App\Rules\DisallowProdUrl;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class HealthCheckRequest extends FormRequest
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
    public function rules(): array
    {
        return [
            'environment.base_url' => ['required', 'url', new DisallowProdUrl],
            'environment.token' => 'required|string',
            'environment.dataset_id' => 'required|string',
            'environment.account_id' => 'required|string',
        ];
    }
}

<?php

namespace App\Http\Requests\Api\V2;

use Illuminate\Contracts\Validation\ValidationRule;

class HealthCheckRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {

        $commonRules = $this->commonRules();
        $additionalRules =
            [
            ];

        return array_merge($commonRules, $additionalRules);
    }
}

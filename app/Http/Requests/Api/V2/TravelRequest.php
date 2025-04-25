<?php

namespace App\Http\Requests\Api\V2;

use Illuminate\Contracts\Validation\ValidationRule;

class TravelRequest extends BaseFormRequest
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
                'data.lat_from' => 'numeric|between:-90,90|required',
                'data.long_from' => 'numeric|between:-180,180|required',
                'data.lat_to' => 'numeric|between:-90,90|required',
                'data.long_to' => 'numeric|between:-180,180|required',
                'data.google_api_key' => 'string|required'
            ];

        return array_merge($commonRules, $additionalRules);
    }
}

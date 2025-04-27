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
        $additionalRules = [
            'data.activityId' => 'string|required',
            'data.longFrom' => 'numeric|between:-180,180|required',
            'data.latTo' => 'numeric|between:-90,90|required',
            'data.longTo' => 'numeric|between:-180,180|required',
            'data.googleApiKey' => 'string|required'
        ];
        return array_merge($commonRules, $additionalRules);
    }
}

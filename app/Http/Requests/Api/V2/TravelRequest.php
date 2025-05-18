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


            /**
             * Starting latitude for travel calculation.
             * Must be between -90 and 90.
             * @var float
             * @example 43.65107
             */
            'data.latFrom' => 'numeric|between:-90,90|required',
            /**
             * Starting longitude for travel calculation.
             * Must be between -180 and 180.
             * @var float
             * @example -79.347015
             */
            'data.longFrom' => 'numeric|between:-180,180|required',

            /**
             * Destination latitude for travel calculation.
             * Must be between -90 and 90.
             * @var float
             * @example 43.65107
             */
            'data.latTo' => 'numeric|between:-90,90|required',

            /**
             * Destination longitude for travel calculation.
             * Must be between -180 and 180.
             * @var float
             * @example -79.3832
             */
            'data.longTo' => 'numeric|between:-180,180|required',

            /**
             * Google API key used for distance matrix or routing.
             * @var string
             * @example "AIzaSyD4Gv..."
             */
            'data.googleApiKey' => 'string|required',
        ];

        return array_merge($commonRules, $additionalRules);
    }

}

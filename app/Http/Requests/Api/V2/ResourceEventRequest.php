<?php

namespace App\Http\Requests\Api\V2;

use App\Enums\EventType;
use Illuminate\Validation\Rules\Enum;

class ResourceEventRequest extends BaseFormRequest
{
    public function rules(): array
    {
        $commonRules = $this->commonRules();

        $additionalRules = [
            /**
             * The type of resource event being reported.
             *
             * Must be one of:
             * - AO: Attention On
             * - AF: Attention Off
             * - BO: Break On
             * - BF: Break Off
             * - CE: CE Mode
             * - FIX: GPS Fix
             * - RO: Logged On
             * - RF: Logged Off
             *
             * @scramble type string
             * @scramble required true
             * @scramble enum ["AO", "AF", "BO", "BF", "CE", "FIX", "RO", "RF"]
             * @scramble example "AO"
             */
            'data.eventType' => ['required', new Enum(EventType::class)],

            /**
             * The latitude of the resource location.
             * Required when eventType is FIX.
             *
             * @scramble type number
             * @scramble format float
             * @scramble required_if data.eventType=FIX
             * @scramble example 43.65107
             */
            'data.lat' => 'numeric|between:-90,90|required_with:data.long|required_if:data.eventType,FIX',

            /**
             * The longitude of the resource location.
             * Required when eventType is FIX.
             *
             * @scramble type number
             * @scramble format float
             * @scramble required_if data.eventType=FIX
             * @scramble example -79.347015
             */
            'data.long' => 'numeric|between:-180,180|required_with:data.lat|required_if:data.eventType,FIX',

            /**
             * The timestamp when the event occurred.
             * ISO 8601 format is recommended.
             *
             * @scramble type string
             * @scramble format date-time
             * @scramble required false
             * @scramble example "2024-12-01T14:30:00Z"
             */
            'data.eventDateTime' => 'date',
            'data.resourceId' => 'string|required'
        ];

        return array_merge($commonRules, $additionalRules);
    }
}

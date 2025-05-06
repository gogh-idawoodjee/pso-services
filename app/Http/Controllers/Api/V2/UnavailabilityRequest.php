<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Requests\Api\V2\BaseFormRequest;

class UnavailabilityRequest extends BaseFormRequest
{
    public function rules(): array
    {
        $commonRules = $this->commonRules();

        $additionalRules = [
            /**
             * The ID of the resource the unavailability applies to.
             *
             * @scramble type string
             * @scramble required true
             * @scramble example "RESOURCE-123"
             */
            'data.resourceId' => 'required|string',

            /**
             * A description of the unavailability (optional).
             *
             * @scramble type string
             * @scramble maxLength 2000
             * @scramble required false
             * @scramble example "Technician is unavailable due to vacation"
             */
            'data.description' => 'string:2000',

            /**
             * The category ID for this unavailability (e.g., vacation, illness).
             *
             * @scramble type string
             * @scramble required true
             * @scramble example "VACATION"
             */
            'data.categoryId' => 'string|required',

            /**
             * Duration of the unavailability in minutes.
             *
             * @scramble type number
             * @scramble minimum 1
             * @scramble required true
             * @scramble example 480
             */
            'data.duration' => 'numeric|gt:0|required',

            /**
             * Optional time zone offset from UTC (e.g., -5 for EST).
             *
             * @scramble type number
             * @scramble minimum -24
             * @scramble maximum 24
             * @scramble required false
             * @scramble example -5
             */
            'data.timeZone' => 'numeric|between:-24,24',

            /**
             * The base start time of the unavailability (ISO 8601 format).
             *
             * @scramble type string
             * @scramble format date-time
             * @scramble required true
             * @scramble example "2025-05-10T08:00"
             */
            'data.baseDateTime' => 'date_format:Y-m-d\TH:i|required',
            /**
             * The rota ID (required only for ARP shifts).
             *
             * @scramble type string
             * @scramble required_if isArpObject=true
             * @scramble example "ROTA-001"
             */
            'data.rotaId' => 'string|required_if:isArpObject,true',
            /**
             * Indicates if this shift is using ARP format.
             *
             * @scramble type boolean
             * @scramble required false
             * @scramble example true
             */
            'data.isArpObject' => 'bool',
        ];

        return array_merge($commonRules, $additionalRules);
    }
}

<?php

namespace App\Http\Requests\Api\V2;

class UnavailabilityRequest extends BaseFormRequest
{
    public function rules(): array
    {
        $commonRules = $this->commonRules();

        $additionalRules = [
            /**
             * The ID of the resource the unavailability applies to.
             * @var string
             * @example "RESOURCE-123"
             */
            'data.resourceId' => 'required|string',

            /**
             * A description of the unavailability (optional). Max 2000 characters.
             * @var string|null
             * @example "Technician is unavailable due to vacation"
             */
            'data.description' => 'string|max:2000',

            /**
             * The category ID for this unavailability (e.g., vacation, illness).
             * @var string
             * @example "VACATION"
             */
            'data.categoryId' => 'string|required',

            /**
             * Duration of the unavailability in minutes.
             * @var int
             * @example 480
             */
            'data.duration' => 'numeric|gt:0|required',

            /**
             * Optional time zone offset from UTC (e.g., -5 for EST).
             * @var float|null
             * @example -5
             */
            'data.timeZone' => 'numeric|between:-24,24',

            /**
             * The base start time of the unavailability (ISO 8601 format).
             * @var string
             * @example "2025-05-10T08:00"
             */
            'data.baseDateTime' => 'date_format:Y-m-d\TH:i|required',
            /**
             * The rota ID (required only for ARP unavailabilities).
             * @var string|null
             * @example "ROTA-001"
             */
            'data.rotaId' => 'string|required_if:data.isArpObject,true',
            /**
             * Indicates if this unavailability is using ARP format.
             * @var bool|null
             * @example true
             */
            'data.isArpObject' => 'bool',
        ];

        return array_merge($commonRules, $additionalRules);
    }
}

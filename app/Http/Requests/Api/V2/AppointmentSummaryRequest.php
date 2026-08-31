<?php

namespace App\Http\Requests\Api\V2;

class AppointmentSummaryRequest extends BaseFormRequest
{
    public function prepareForValidation(): void
    {
        if ($id = $this->route('appointmentRequestId')) {
            $this->merge([
                'data' => array_merge($this->input('data', []), [
                    'appointmentRequestId' => $id,
                ]),
            ]);
        }
    }

    public function rules(): array
    {
        $commonRules = $this->commonRules();

        $additionalRules = [
            /**
             * The ID of the appointment offer.
             * Not required when declining (DELETE) — the offer is ignored for that operation.
             *
             * @var int
             *
             * @example 12345
             */
            'data.appointmentOfferId' => $this->isMethod('delete') ? 'sometimes|integer' : 'integer|required',

            /**
             * The ID of the appointment request.
             *
             * @var string
             *
             * @example "req-67890"
             */
            'data.appointmentRequestId' => 'string|required',

            /**
             * Multiplier applied to the activity's base_value when accepting an offer, so the
             * accepted appointment resists displacement by newly-arriving activities. Only used
             * on accept (PATCH); ignored otherwise. Must be greater than 1 — anything else would
             * lower the value instead of raising it. Defaults to the configured value when omitted.
             *
             * @var float
             *
             * @example 1.5
             */
            'data.acceptedValueMultiplier' => 'sometimes|numeric|gt:1',
        ];

        return array_merge($commonRules, $additionalRules);
    }

    public function bodyParameters(): array
    {
        return array_merge($this->commonBodyParameters(), [
            'data.appointmentOfferId' => [
                'description' => 'The ID of the appointment offer. Not required when declining (DELETE) — the offer is ignored for that operation.',
                'example' => 12345,
            ],
            'data.appointmentRequestId' => [
                'description' => 'The ID of the appointment request.',
                'example' => 'req-67890',
            ],
            'data.acceptedValueMultiplier' => [
                'description' => 'Multiplier applied to base_value when accepting an offer, so the accepted appointment resists displacement. Only used on accept (PATCH); must be > 1. Defaults to the configured value when omitted.',
                'example' => 1.5,
            ],
        ]);
    }
}

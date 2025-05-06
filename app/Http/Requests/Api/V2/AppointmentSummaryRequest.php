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
             * @var int
             * @example 12345
             */
            'data.appointmentOfferId' => 'integer|required',

            /**
             * The ID of the appointment request.
             * @var string
             * @example "req-67890"
             */
            'data.appointmentRequestId' => 'string|required'
        ];

        return array_merge($commonRules, $additionalRules);
    }
}

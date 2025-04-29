<?php

namespace App\Http\Requests\Api\V2;

class AppointedRequest extends BaseFormRequest
{
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

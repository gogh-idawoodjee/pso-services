<?php

namespace App\Helpers\Stubs;


class AppointmentOfferResponse
{

    public static function make(string|null $appointmentRequestId, int|null $appointmentOfferId = -1, bool $accepted = false, int $psoApiVersion = 1): array
    {

        return  [
            'appointment_request_id' => $appointmentRequestId,
            'appointment_offer_id' => $appointmentOfferId,
            'input_updated' => $accepted
        ];



    }
}

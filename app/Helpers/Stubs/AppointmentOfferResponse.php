<?php

namespace App\Helpers\Stubs;


use App\Enums\InputMode;
use App\Helpers\PSOHelper;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;


class AppointmentOfferResponse
{

    public static function make(string|null $appointmentRequestId, int|null $appointmentOfferId = -1, bool $accepted = false, int $psoApiVersion = 1): array
    {

        $appointmentOfferResponse = [
            'appointment_request_id' => $appointmentRequestId,
            'appointment_offer_id' => $appointmentOfferId ,
            'input_updated' => $accepted
        ];

        return ['Appointment_Offer_response' => $appointmentOfferResponse];

    }
}

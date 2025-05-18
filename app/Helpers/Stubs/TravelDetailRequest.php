<?php

namespace App\Helpers\Stubs;

use DateTime;

class TravelDetailRequest
{
    public static function make(
        string        $id,
        float         $latFrom,
        float         $longFrom,
        float         $latTo,
        float         $longTo,
        string|null   $travelProfileId = null,
        DateTime|null $startDateTime = null,
        int           $psoApiVersion = 1
    ): array
    {
        return array_filter([
            'id' => $id,
            'latitude_from' => $latFrom,
            'longitude_from' => $longFrom,
            'latitude_to' => $latTo,
            'longitude_to' => $longTo,
            'travel_profile_id' => $travelProfileId,
            'start_date_time' => $startDateTime
        ], static fn($value) => $value !== null);

    }
}

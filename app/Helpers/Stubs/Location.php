<?php

namespace App\Helpers\Stubs;



class Location
{
    public static function make(
        string $id,
        float $latitude,
        float $longitude,
        string|null $locality = null,
        int         $psoApiVersion = 1
    ): array {
        $location = compact('id', 'latitude', 'longitude');

        if ($locality) {
            $location['locality'] = $locality;
        }

        return $location;
    }
}

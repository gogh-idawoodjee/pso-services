<?php

namespace App\Helpers\Stubs;



class Location
{
    public static function make(
        string $id,
        float $latitude,
        float $longitude,
        string|null $locality = null
    ): array {
        $location = compact('id', 'latitude', 'longitude');

        if ($locality) {
            $location['locality'] = $locality;
        }

        return $location;
    }
}

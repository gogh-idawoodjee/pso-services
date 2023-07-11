<?php

namespace App\Classes;


class PSOLocation
{

    private float $latitude;
    private float $longitude;
    private string $locality;


    public function __construct($lat, $long, $locality = null)
    {
        $this->latitude = $lat;
        $this->longitude = $long;
        $this->locality = $locality;
    }

    public function toJson($id) // changed from $activity_id
    {
        return [
            'id' => $id, // changed from $activity_id
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'locality' => $this->locality
        ];
    }

}

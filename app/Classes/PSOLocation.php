<?php

namespace App\Classes;


class PSOLocation
{

    private float $latitude;
    private float $longitude;


    public function __construct($lat, $long)
    {
        $this->latitude = $lat;
        $this->longitude = $long;
    }

    public function toJson($id) // changed from $activity_id
    {
        return [
            'id' => $id, // changed from $activity_id
            'latitude' => $this->latitude,
            'longitude' => $this->longitude
        ];
    }

}

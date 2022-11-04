<?php

namespace App\Classes;


class PSOLocation extends Activity
{


    private float $latitude;
    private float $longitude;


    public function __construct($lat, $long)
    {
        $this->latitude = $lat;
        $this->longitude = $long;


    }

    public function toJson($activity_id)
    {
        return [
            'id' => $activity_id,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude
        ];
    }

}

<?php


namespace App\Classes\V1;

use Illuminate\Support\Arr;

class PSOLocation
{

    private float $latitude;
    private float $longitude;
    private string|null $locality;


    public function __construct($lat, $long, $locality = null)
    {
        $this->latitude = $lat;
        $this->longitude = $long;
        $this->locality = $locality;
    }

    public function toJson($id) // changed from $activity_id
    {
        $json = [
            'id' => $id, // changed from $activity_id
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,

        ];
        if ($this->locality) {
            $json = Arr::add($json, 'locality', $this->locality);
        }
        return $json;
    }

}

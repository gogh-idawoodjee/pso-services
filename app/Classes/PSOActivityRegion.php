<?php

namespace App\Classes;


class PSOActivityRegion extends Activity
{

    private string $region_id;

    public function __construct($region_id)
    {
        $this->region_id = $region_id;
    }

    public function toJson($activity_id)
    {
        return [
            'location_id' => $activity_id,
            'region_id' => $this->region_id
        ];
    }

}

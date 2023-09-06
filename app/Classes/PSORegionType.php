<?php

namespace App\Classes;


use Illuminate\Support\Str;

class PSORegionType
{

    private string $region_type_id;
    private string|null $description;


    public function __construct($region_type_id, $description = null)
    {
        $this->region_type_id = $region_type_id;
        $this->description = $description;

    }

    public function RAMtoJson()
    {
        // used for modelling
        return [
            'id' => $this->region_type_id,
            'description' => $this->description ?: Str::lower($this->region_type_id)
        ];

    }

}

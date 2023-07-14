<?php

namespace App\Classes;


use Illuminate\Support\Str;

class PSORegion
{

    private string $region_id;
    private string $entity_type;
    private ?string $description;
    private ?bool $send;
    private ?string $ram_division_id;
    private ?string $ram_division_type_id;


    public function __construct($region_id, $entity_type = "activity", $description = null, $send = true, $ram_division_id = null, $ram_division_type_id = null)
    {
        $this->region_id = $region_id;
        $this->entity_type = $entity_type;
        $this->description = $description;
        $this->send = $send;
        $this->ram_division_type_id = $ram_division_type_id;
        $this->ram_division_id = $ram_division_id;

    }

    public function toJson($id)
    {
        return [
            $this->entity_type == 'activity' ? 'location_id' : 'ram_resource_id' => $id,
            $this->entity_type == 'activity' ? 'region_id' : 'ram_division_id' => $this->region_id
        ];
    }

    public function RAMtoJson()
    {
        // used for modelling
        return [
            'id' => $this->region_id,
            'description' => $this->description ?: Str::lower($this->region_id),
            'send' => $this->send,
            'ram_division_id' => $this->ram_division_id,
            'ram_division_type_id' => $this->ram_division_type_id,
        ];

    }

}

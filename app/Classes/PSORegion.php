<?php

namespace App\Classes;


class PSORegion
{

    private string $region_id;
    private string $entity_type;

    public function __construct($region_id, $entity_type = "activity")
    {
        $this->region_id = $region_id;
        $this->entity_type = $entity_type;

    }

    public function toJson($id)
    {
        return [
            $this->entity_type == 'activity' ? 'location_id' : 'ram_resource_id' => $id,
            $this->entity_type == 'activity' ? 'region_id' : 'ram_division_id' => $this->region_id
        ];
    }

}

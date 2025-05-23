<?php

namespace App\Classes\V1;

class PSOSkill
{

    private string $skill_id;
    private string $entity_type;

    public function __construct($skill_id, $entity_type = "activity")
    {
        $this->skill_id = $skill_id;
        $this->entity_type = $entity_type;
    }

    public function toJson($id)
    {

        return [
            $this->entity_type == "activity" ? 'skill_id' : 'ram_skill_id' => $this->skill_id,
            $this->entity_type == "activity" ? "activity_id" : "ram_resource_id" => $id
        ];
    }
}

<?php

namespace App\Classes;

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
            'skill_id' => $this->skill_id,
            $this->entity_type == "activity" ? "activity_id" : "resource_id" => $id
        ];
    }
}

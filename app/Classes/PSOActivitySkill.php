<?php

namespace App\Classes;


class PSOActivitySkill extends Activity
{


    private string $skill_id;


    public function __construct($skill_id)
    {
        $this->skill_id = $skill_id;
    }

    public function toJson($activity_id)
    {
        return [
            'activity_id' => $activity_id,
            'skill_id' => $this->skill_id
        ];
    }
}

<?php

namespace App\Helpers\Stubs;


class Skill
{
    public static function make(string $skillId, string $entityType = 'activity', string $entityId): array
    {
        return [
            $entityType === 'activity' ? 'skill_id' : 'ram_skill_id' => $skillId,
            $entityType === 'activity' ? 'activity_id' : 'ram_resource_id' => $entityId,
        ];
    }
}

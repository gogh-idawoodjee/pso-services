<?php

namespace App\Helpers\Stubs;


class Skill
{
    public static function make(string $skillId, string $entityId, string $entityType = 'activity', int $psoApiVersion = 1): array
    {
        return match ($entityType) {
            'activity' => ['skill_id' => $skillId, 'activity_id' => $entityId],
            default => ['ram_skill_id' => $skillId, 'ram_resource_id' => $entityId],
        };
    }
}

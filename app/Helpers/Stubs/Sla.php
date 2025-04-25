<?php

namespace App\Helpers\Stubs;


class Sla
{
    public static function make(
        string $activityId,
        string $slaTypeId,
        string $datetimeStart,
        string $datetimeEnd,
        int    $priority = 2,
        bool   $startBased = true,
        int    $psoApiVersion = 1
    ): array
    {
        return [
            'activity_id' => $activityId,
            'sla_type_id' => $slaTypeId,
            'datetime_start' => $datetimeStart,
            'datetime_end' => $datetimeEnd,
            'priority' => $priority,
            'start_based' => $startBased,
        ];
    }
}

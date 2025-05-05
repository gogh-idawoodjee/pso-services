<?php

namespace App\Helpers\Stubs;

class Shift
{
    public static function make(
        string      $shiftId,
        string      $resourceId,
        string|null $rotaId = null,
                    $startDateTime = null,
                    $endDateTime = null,
        bool|null   $isManualSchedulingOnly = null,
        string|null $shiftType = null,
        string|null $description = null,
        bool|null   $isArpShift = null,
        int         $psoApiVersion = 1
    ): array
    {


        return [
            'id' => $shiftId,
            'manual_scheduling_only' => $isManualSchedulingOnly ?? false,
            'start_datetime' => $startDateTime,
            'end_datetime' => $endDateTime,
            'description' => $description,
            ...$shiftType
                ? [$isArpShift ? 'ram_shift_category_id' : 'shift_type_id' => $shiftType]
                : [],
            $isArpShift ? 'ram_rota_id' : 'rota_id' => $rotaId,
            $isArpShift ? 'ram_resource_id' : 'resource_id' => $resourceId,
        ];

    }
}

<?php

namespace App\Helpers\Stubs\Deprecated;


use App\Enums\EventType;
use Date;
use Illuminate\Support\Str;

/**
 * @deprecated Use App\Builders\ActivityBuilder instead.
 * This class will be removed in an upcoming release.
 */
class ResourceEvent
{
    public static function make(
        string     $resourceId,
        EventType  $eventType,
        Date|null  $eventDateTime = null,
        float|null $latitude = null,
        float|null $longitude = null,
        int        $psoApiVersion = 1
    ): array
    {

        return [
            'id' => (string)Str::orderedUuid(),
            'date_time_stamp' => now()->toAtomString(),
            'event_date_time' => $eventDateTime ?? now()->toAtomString(),
            'event_type_id' => $eventType->value,
            'resource_id' => $resourceId,
            ...filled($latitude) && filled($longitude) ? compact('latitude', 'longitude') : [],
        ];

    }
}

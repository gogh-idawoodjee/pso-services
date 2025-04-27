<?php

namespace App\Helpers\Stubs;


use Illuminate\Support\Str;

class Region
{
    public static function make(string $regionId, string $entityId, string|null $entityType = 'activity', int $psoApiVersion = 1): array
    {
        $entityType ??= 'region';
        return [
            $entityType === 'activity' ? 'location_id' : 'ram_resource_id' => $entityId,
            $entityType === 'activity' ? 'region_id' : 'ram_division_id' => $regionId,
        ];
    }

    public
    static function makeRAMDivision(
        string      $regionId,
        string|null $description = null,
        bool|null   $send = null,
        string|null $ramDivisionId = null,
        string|null $ramDivisionTypeId = null
    ): array
    {
        $send ??= true;
        $json = [
            'id' => $regionId,
            'description' => $description ?: Str::lower($regionId),
            'send' => $send,
        ];

        if ($ramDivisionTypeId) {
            $json['ram_division_type_id'] = $ramDivisionTypeId;
        }

        if ($ramDivisionId) {
            $json['ram_division_id'] = $ramDivisionId;
        }

        return $json;
    }
}

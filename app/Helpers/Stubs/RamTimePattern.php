<?php

namespace App\Helpers\Stubs;


use Illuminate\Support\Str;

class RamTimePattern
{
    public static function make(
        string      $resourceId,
        string      $timePatternId,
        string      $category_id,
        string|null $description = null,
        int         $psoApiVersion = 1
    ): array
    {

        return array_filter([
            'id' => Str::uuid()->getHex(),
            'ram_time_pattern_id' => $timePatternId,
            'ram_resource_id' => $resourceId,
            'ram_unavailability_category_id' => $category_id,
            'description' => $description,
        ], static fn($value) => $value !== null);


    }
}

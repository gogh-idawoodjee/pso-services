<?php

namespace App\Helpers\Stubs;


use Illuminate\Support\Str;

class Unavailability
{
    public static function make(
        string      $resourceId,

        string|null $description = null,
        bool|null   $isArpObject = null,
        int         $psoApiVersion = 1
    ): array
    {

        if ($isArpObject) {
            $resourceKey = 'ram_resource_id';
            $categoryKey = 'ram_unavailability_category_id';

        } else {
            $resourceKey = 'resource_id';
            $categoryKey = 'category_id';

        }


        return [
            'id' => Str::uuid()->getHex(),
            'ram_time_pattern_id' => $time_pattern_id,
            'ram_resource_id' => $resource_id,
            'ram_unavailability_category_id' => (string)$category_id,
            'description' => (string)$description
        ];

    }
}

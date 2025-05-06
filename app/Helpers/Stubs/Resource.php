<?php

namespace App\Helpers\Stubs;


use Illuminate\Support\Str;

class Resource
{
    public static function make(object $resourceData, float $lat, float $long, int $psoApiVersion = 1): array
    {
        $resourceId = $resourceData->resource_id
            ?? Str::upper($resourceData->first_name . $resourceData->surname);

        $resource = [
            'id' => $resourceId,
            'ram_resource_class_id' => config('pso-services.defaults.resource.class_id'),
            'ram_resource_type_id' => $resourceData->resource_type_id,
            'ram_location_id_start' => $resourceId,
            'ram_location_id_end' => $resourceId,
            'first_name' => $resourceData->first_name,
            'surname' => $resourceData->surname,
        ];

        $resourceSkills = collect($resourceData->skill ?? [])
            ->map(static fn($skill) => Skill::make($skill, 'resource', $resourceId))
            ->values()
            ->toArray();

        $resourceRegions = collect($resourceData->region ?? [])
            ->map(static fn($region) => Region::make($region, 'resource', $resourceId))
            ->values()
            ->toArray();

        $location = Location::make($resourceId, $lat, $long);

        return [
            'RAM_resource' => $resource,
            'Location' => $location,
            'RAM_Resource_Division' => $resourceRegions,
            'RAM_Resource_Skill' => $resourceSkills,
        ];
    }
}

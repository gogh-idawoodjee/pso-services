<?php

namespace App\Helpers\Stubs;

use App\Classes\V2\PSOObjectRegistry;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class DeleteObject
{
    public static function make(
        array $data,
        bool  $isRotaObject = false,
        int   $psoApiVersion = 1
    ): array
    {
        $label = data_get($data, 'objectType');

        if (!$label) {
            Log::error('Object type is missing from request.');
            throw new RuntimeException('Object type is missing from request.');
        }

        $key = collect(PSOObjectRegistry::all())
            ->filter(static fn($entry) => strcasecmp($entry['label'], $label) === 0)
            ->keys()
            ->first();

        if (!$key) {
            Log::error("Object type '{$label}' not found in registry.");
            throw new RuntimeException("Object type '{$label}' not found in registry.");

        }

        $registry = PSOObjectRegistry::get($key);

        if (!$registry) {
            Log::error("Registry entry for '{$label}' not found.");
            throw new RuntimeException("Registry entry for '{$label}' not found.");

        }

        $payload = collect($registry['attributes'] ?? [])
            ->sortBy('name')
            ->values()
            ->mapWithKeys(static function ($attribute, $i) use ($data) {
                $index = $i + 1;
                $attributeName = data_get($attribute, 'name', "attribute{$index}");
                $value = data_get($data, "objectPk{$index}");

                if ($value === null) {
                    Log::error("Missing required field objectPk{$index} ({$attributeName}).");
                    throw new RuntimeException("Missing required field objectPk{$index} ({$attributeName}).");
                }

                return [
                    "object_pk{$index}" => $value,
                    "object_pk_name{$index}" => $attributeName,
                ];
            })->all();

        $payload['object_type_id'] = data_get($registry, 'entity');

        if ($isRotaObject) {
            $payload['delete_row'] = true;
        }

        return $payload;
    }

}



/* usage

$primaryKeys = [
    ['name' => 'id', 'value' => 'ACT-001'],
    ['name' => 'activity_type_id', 'value' => 'TYPE-A'],
    ['name' => 'region_id', 'value' => 'REG-7'],
];

$payload = PSODeleteObjectHelper::fromPkArray(
    objectTypeId: 'Activity',
    primaryKeys: $primaryKeys,
    isRotaObject: true
);

// result
[
    'object_type_id' => 'Activity',
    'object_pk_name' => 'id',
    'object_pk' => 'ACT-001',
    'object_pk_name2' => 'activity_type_id',
    'object_pk2' => 'TYPE-A',
    'object_pk_name3' => 'region_id',
    'object_pk3' => 'REG-7',
    'delete_row' => true
]

*/

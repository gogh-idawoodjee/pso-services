<?php

namespace App\Helpers\Stubs;

use App\Classes\PSOObjectRegistry;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class DeleteObject
{

//    public static function fromPkArray(
//        string $objectTypeId,
//        array  $primaryKeys,
//        bool   $isRotaObject = false,
//        int    $psoApiVersion = 1
//    ): array
//    {
//        $data = ['object_type_id' => $objectTypeId];
//
//        foreach ($primaryKeys as $index => $pk) {
//            $suffix = $index === 0 ? '' : (string)($index + 1);
//            $data["object_pk_name{$suffix}"] = $pk['name'];
//            $data["object_pk{$suffix}"] = $pk['value'];
//        }
//
//        if ($isRotaObject) {
//            $data['delete_row'] = true;
//        }
//
//        return $data;
//    }

    public static function make(
        array $data,
        bool  $isRotaObject = false,
        int   $psoApiVersion = 1
    ): array
    {
        $objectType = $data['object_type'] ?? null;

        if (!$objectType) {
            throw new RuntimeException('Object type is missing from request.');
        }

        $registry = PSOObjectRegistry::get($objectType);

        if (!$registry) {
            throw new RuntimeException("Object type '{$objectType}' not found in registry.");
        }

        $payload = [];
        $attributes = $registry['attributes'] ?? [];

        $attributes = collect($attributes)->sortBy('name')->values()->all();


        foreach ($attributes as $index => $attribute) {
            $pkIndex = $index + 1;

            $pkField = "object_pk{$pkIndex}";
            $attributeName = $attribute['name'];

            if (!array_key_exists($pkField, $data)) {
                throw new RuntimeException("Missing required field {$pkField} ({$attributeName}).");
            }

            $payload["object_pk{$pkIndex}"] = $data[$pkField];
            $payload["object_pk_name{$pkIndex}"] = $attributeName;
        }

        // Add the object_type using the Entity name
        $payload['object_type'] = $registry['entity'];

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

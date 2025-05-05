<?php

namespace App\Helpers\Stubs;

use App\Classes\PSOObjectRegistry;
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
    ): array {
        $label = data_get($data, 'objectType');

        if (!$label) {
            throw new RuntimeException('Object type is missing from request.');
        }

        // 🔁 Resolve label to registry key
        $key = collect(PSOObjectRegistry::all())
            ->filter(fn($entry) => strtolower($entry['label']) === strtolower($label))
            ->keys()
            ->first();

        if (!$key) {
            throw new RuntimeException("Object type '{$label}' not found in registry.");
        }

        $registry = PSOObjectRegistry::get($key);

        if (!$registry) {
            throw new RuntimeException("Registry entry for '{$key}' not found.");
        }

        $payload = [];
        $attributes = collect($registry['attributes'] ?? [])
            ->sortBy('name')
            ->values()
            ->all();

        foreach ($attributes as $index => $attribute) {
            $pkIndex = $index + 1;

            $camelPkField = "objectPk{$pkIndex}";
            $snakePkField = "object_pk{$pkIndex}";
            $snakePkNameField = "object_pk_name{$pkIndex}";
            $attributeName = data_get($attribute, 'name', "attribute{$pkIndex}");

            $value = data_get($data, $camelPkField);

            if ($value === null) {
                throw new RuntimeException("Missing required field {$camelPkField} ({$attributeName}).");
            }

            $payload[$snakePkField] = $value;
            $payload[$snakePkNameField] = $attributeName;
        }

        // 👇 Use entity name as required by PSO
        $payload['objectType'] = data_get($registry, 'entity');

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

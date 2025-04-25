<?php

namespace App\Helpers\Stubs;

class DeleteObject
{
    /**
     * @param string $objectTypeId
     * @param array{name: string, value: mixed}[] $primaryKeys
     * @param bool $isRotaObject
     * @return array
     */
    public static function fromPkArray(
        string $objectTypeId,
        array  $primaryKeys,
        bool   $isRotaObject = false,
        int    $psoApiVersion = 1
    ): array
    {
        $data = ['object_type_id' => $objectTypeId];

        foreach ($primaryKeys as $index => $pk) {
            $suffix = $index === 0 ? '' : (string)($index + 1);
            $data["object_pk_name{$suffix}"] = $pk['name'];
            $data["object_pk{$suffix}"] = $pk['value'];
        }

        if ($isRotaObject) {
            $data['delete_row'] = true;
        }

        return $data;
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

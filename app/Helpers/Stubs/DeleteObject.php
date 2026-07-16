<?php

namespace App\Helpers\Stubs;

use Illuminate\Support\Facades\Log;
use RuntimeException;

class DeleteObject
{
    /**
     * Builds an Object_Deletion payload from a registry entry already resolved by the caller.
     * Callers own object-type validation (see DeleteService::deleteObject()) — this only
     * validates that the primary key fields the registry expects are actually present.
     */
    public static function make(
        array $registry,
        array $data,
        bool  $isRotaObject = false,
    ): array
    {
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

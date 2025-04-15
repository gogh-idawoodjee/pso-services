<?php

namespace App\Helpers\Stubs;


use Illuminate\Support\Str;

class RegionType
{
    public static function make(string $regionTypeId, string|null $description = null): array
    {
        return [
            'id' => $regionTypeId,
            'description' => $description ?: Str::lower($regionTypeId),
        ];
    }
}

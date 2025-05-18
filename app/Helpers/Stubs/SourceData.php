<?php

namespace App\Helpers\Stubs;


use App\Constants\PSOConstants;

class SourceData
{
    public static function make(
        int $psoApiVersion = 1
    ): array
    {
        return [
            'source_data_type_id' => PSOConstants::ARP_SOURCE_DATATYPE,
        ];
    }
}

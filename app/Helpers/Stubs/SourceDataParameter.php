<?php

namespace App\Helpers\Stubs;


use App\Constants\PSOConstants;

class SourceDataParameter
{
    public static function make(
        string $parameterName,
        string $parameterValue,
        int    $psoApiVersion = 1
    ): array
    {
        return [
            'source_data_type_id' => PSOConstants::ARP_SOURCE_DATATYPE,
            'parameter_name' => $parameterName,
            'parameter_value' => $parameterValue,
        ];
    }
}

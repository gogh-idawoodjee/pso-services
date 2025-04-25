<?php

namespace App\Helpers\Stubs;

use App\Enums\BroadcastParameterType;

class BroadcastParameter
{
    public static function make(string $broadcastId, BroadcastParameterType $parameterName, string $parameterValue): array
    {
        return [
            'broadcast_id' => $broadcastId,
            'parameter_name' => $parameterName,
            'parameter_value' => $parameterValue,
        ];
    }
}

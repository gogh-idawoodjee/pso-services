<?php

namespace App\Helpers\Stubs;

class BroadcastParameter
{
    public static function make(
        string $broadcastId,
        string $parameterName,
        string $parameterValue,
    ): array {
        return [
            'broadcast_id' => $broadcastId,
            'parameter_name' => $parameterName,
            'parameter_value' => $parameterValue,
        ];
    }
}

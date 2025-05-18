<?php

namespace App\Helpers\Stubs;

use App\Enums\BroadcastParameterType;

class BroadcastParameter
{
    public function __construct(
        public string $broadcastId,
        public BroadcastParameterType $parameterName,
        public string $parameterValue
    ) {}

    public function toArray(): array
    {
        return [
            'broadcast_id' => $this->broadcastId,
            'parameter_name' => $this->parameterName->value,
            'parameter_value' => $this->parameterValue,
        ];
    }
}

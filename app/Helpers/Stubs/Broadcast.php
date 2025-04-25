<?php

namespace App\Helpers\Stubs;

use App\Enums\BroadcastAllocationType;
use App\Enums\BroadcastParameterType;
use App\Enums\BroadcastPlanType;
use Illuminate\Support\Str;

class Broadcast
{
    public static function make(
        BroadcastAllocationType $broadcastAllocationType,
        array                   $broadcastParameters,
        string                  $broadcastType = 'REST',
        bool                    $onceOnly = false,
        BroadcastPlanType       $broadcastPlanType = BroadcastPlanType::COMPLETE
    ): array
    {
        $broadcast_id = Str::orderedUuid()->getHex()->toString();

        $parameters = array_map(
            static fn($parameter) => BroadcastParameter::make(
                $broadcast_id,
                BroadcastParameterType::from($parameter['parameter_name']),
                $parameter['parameter_value']
            ),
            $broadcastParameters
        );

        return [
            [
                'active' => true,
                'broadcast_id' => $broadcast_id,
                'broadcast_allocation_type' => $broadcastAllocationType->value,
                'broadcast_type' => $broadcastType,
                'once_only' => $onceOnly,
                'broadcast_plan_type' => $broadcastPlanType->value,
            ],
            'Broadcast_Parameter' => $parameters
        ];
    }
}

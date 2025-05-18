<?php

namespace App\Classes\V2\EntityBuilders;

use App\Enums\BroadcastAllocationType;
use App\Enums\BroadcastPlanType;
use App\Enums\BroadcastParameterType;
use App\Helpers\Stubs\BroadcastParameter;
use Illuminate\Support\Str;

class BroadcastBuilder
{
    protected BroadcastAllocationType $broadcastAllocationType;
    protected array $broadcastParameters = [];
    protected string $broadcastType = 'REST';
    protected bool $onceOnly = false;
    protected BroadcastPlanType $broadcastPlanType = BroadcastPlanType::COMPLETE;

    public static function make(): static
    {
        return new static();
    }

    public function allocationType(BroadcastAllocationType $type): static
    {
        $this->broadcastAllocationType = $type;
        return $this;
    }

    public function parameters(array $params): static
    {
        $this->broadcastParameters = $params;
        return $this;
    }

    public function type(string $type): static
    {
        $this->broadcastType = $type;
        return $this;
    }

    public function onceOnly(bool $flag = true): static
    {
        $this->onceOnly = $flag;
        return $this;
    }

    public function planType(BroadcastPlanType $type): static
    {
        $this->broadcastPlanType = $type;
        return $this;
    }

    public function build(): array
    {
        $broadcast_id = Str::orderedUuid()->getHex()->toString();

        $parameters = array_map(static function ($param) use ($broadcast_id) {
            if ($param instanceof BroadcastParameterBuilder) {
                return $param->finalize($broadcast_id)->toArray();
            }

            if ($param instanceof BroadcastParameter) {
                return $param->toArray();
            }

            // Raw array fallback
            return (new BroadcastParameter(
                $broadcast_id,
                BroadcastParameterType::from($param['parameter_name']),
                $param['parameter_value']
            ))->toArray();
        }, $this->broadcastParameters);

        return [
            'Broadcast' => [
                'active' => true,
                'id' => $broadcast_id,
                'allocation_type' => $this->broadcastAllocationType->value,
                'broadcast_type_id' => $this->broadcastType,
                'once_only' => $this->onceOnly,
                'plan_type' => $this->broadcastPlanType->value,
            ],
            'Broadcast_Parameter' => $parameters,
        ];
    }
}

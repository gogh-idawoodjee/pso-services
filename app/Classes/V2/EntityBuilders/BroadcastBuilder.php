<?php

namespace App\Classes\V2\EntityBuilders;

use App\Enums\BroadcastAllocationType;
use App\Enums\BroadcastPlanType;
use Illuminate\Support\Str;

class BroadcastBuilder
{
    protected BroadcastAllocationType $broadcastAllocationType;
    /** @var BroadcastParameterBuilder[] */
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

    /** @param BroadcastParameterBuilder[] $params */
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

        $parameters = array_map(
            static fn(BroadcastParameterBuilder $param) => $param->finalize($broadcast_id),
            $this->broadcastParameters,
        );

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

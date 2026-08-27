<?php

namespace App\Classes\V2\EntityBuilders;

use App\Enums\BroadcastParameterType;
use App\Helpers\Stubs\BroadcastParameter;

class BroadcastParameterBuilder
{
    protected string|BroadcastParameterType|null $name = null;

    protected ?string $value = null;

    public static function make(): static
    {
        return new static;
    }

    public function name(string|BroadcastParameterType $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function value(string $value): static
    {
        $this->value = $value;

        return $this;
    }

    public function finalize(string $broadcastId): array
    {
        return BroadcastParameter::make(
            $broadcastId,
            $this->name instanceof BroadcastParameterType ? $this->name->value : $this->name,
            $this->value,
        );
    }
}

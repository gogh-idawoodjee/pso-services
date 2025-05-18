<?php

namespace App\Classes\V2\EntityBuilders;

use App\Enums\BroadcastParameterType;
use App\Helpers\Stubs\BroadcastParameter;

class BroadcastParameterBuilder
{
    protected BroadcastParameterType|null $name = null;
    protected string|null $value = null;
    protected string|null $broadcastId = null;

    public static function make(): static
    {
        return new static();
    }

    public function name(BroadcastParameterType $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function value(string $value): static
    {
        $this->value = $value;
        return $this;
    }

    public function finalize(string $broadcastId): BroadcastParameter
    {
        return new BroadcastParameter(
            $broadcastId,
            $this->name,
            $this->value
        );
    }
}

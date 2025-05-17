<?php

namespace App\Classes\V2\EntityBuilders;

use App\Enums\EventType;
use DateTimeInterface;
use Illuminate\Support\Str;

class ResourceEventBuilder
{
    protected string|null $resourceId = null;
    protected EventType|null $eventType = null;
    protected DateTimeInterface|null $eventDateTime = null;
    protected float|null $latitude = null;
    protected float|null $longitude = null;
    protected int $psoApiVersion = 1;

    // Static constructor to start the chain
    public static function make(string $resourceId, EventType $eventType): self
    {
        $instance = new self();
        $instance->resourceId = $resourceId;
        $instance->eventType = $eventType;
        return $instance;
    }

    // Chainable setters for optional params
    public function eventDateTime(DateTimeInterface|null $eventDateTime): self
    {
        $this->eventDateTime = $eventDateTime;
        return $this;
    }

    public function latitude(float|null $latitude): self
    {
        $this->latitude = $latitude;
        return $this;
    }

    public function longitude(float|null $longitude): self
    {
        $this->longitude = $longitude;
        return $this;
    }

    public function psoApiVersion(int $version): self
    {
        $this->psoApiVersion = $version;
        return $this;
    }

    // The final build method compiles everything into the array
    public function build(): array
    {
        return [
            'id' => (string) Str::orderedUuid(),
            'date_time_stamp' => now()->toAtomString(),
            'event_date_time' => $this->eventDateTime?->toAtomString() ?? now()->toAtomString(),
            'event_type_id' => $this->eventType->value,
            'resource_id' => $this->resourceId,
            ...filled($this->latitude) && filled($this->longitude) ? ['latitude' => $this->latitude, 'longitude' => $this->longitude] : [],
        ];
    }
}

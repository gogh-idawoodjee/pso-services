<?php

namespace App\Classes\V2\EntityBuilders;

use App\Enums\EventType;
use Carbon\Carbon;            // <— add this
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

    public static function make(string $resourceId, EventType $eventType): self
    {
        $instance = new self();
        $instance->resourceId = $resourceId;
        $instance->eventType = $eventType;
        return $instance;
    }

    // Accept string|numeric too, normalize to DateTimeInterface
    public function eventDateTime(DateTimeInterface|string|int|float|null $eventDateTime): self
    {
        if ($eventDateTime === null) {
            $this->eventDateTime = null;
            return $this;
        }

        if ($eventDateTime instanceof DateTimeInterface) {
            $this->eventDateTime = $eventDateTime;
            return $this;
        }

        // Excel serials occasionally show up — support them
        if (is_numeric($eventDateTime)) {
            // Excel epoch 1899-12-30; seconds per day = 86400
            $timestamp = ((float)$eventDateTime - 25569) * 86400;
            $this->eventDateTime = Carbon::createFromTimestampUTC((int)$timestamp)->setTimezone(config('app.timezone'));
            return $this;
        }

        // Strings like "2025-10-01T12:00:00-04:00", "2025-10-01 12:00:00", etc.
        $this->eventDateTime = Carbon::parse($eventDateTime);
        return $this;
    }

    // Be lenient on input types and cast
    public function latitude(float|int|string|null $latitude): self
    {
        $this->latitude = $latitude === null || $latitude === '' ? null : (float)$latitude;
        return $this;
    }

    public function longitude(float|int|string|null $longitude): self
    {
        $this->longitude = $longitude === null || $longitude === '' ? null : (float)$longitude;
        return $this;
    }

    public function psoApiVersion(int $version): self
    {
        $this->psoApiVersion = $version;
        return $this;
    }

    public function build(): array
    {
        return [
            'id'              => (string) Str::orderedUuid(),
            'date_time_stamp' => now()->toAtomString(),
            'event_date_time' => $this->eventDateTime?->toAtomString() ?? now()->toAtomString(),
            'event_type_id'   => $this->eventType->value,
            'resource_id'     => $this->resourceId,
            ...filled($this->latitude) && filled($this->longitude)
                ? ['latitude' => $this->latitude, 'longitude' => $this->longitude]
                : [],
        ];
    }
}

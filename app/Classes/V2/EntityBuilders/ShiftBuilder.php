<?php

namespace App\Classes\V2\EntityBuilders;

use InvalidArgumentException;

class ShiftBuilder
{
    protected string|null $shiftId = null;
    protected string|null $resourceId = null;
    protected string|null $rotaId = null;
    protected string|null $startDateTime = null;
    protected string|null $endDateTime = null;
    protected bool $isManualSchedulingOnly = false;
    protected string|null $shiftType = null;
    protected string|null $description = null;
    protected bool $isArpObject = false;
    protected int $psoApiVersion = 1;

    public static function make(): self
    {
        return new self();
    }

    public function shiftId(string $shiftId): self
    {
        $this->shiftId = $shiftId;
        return $this;
    }

    public function resourceId(string $resourceId): self
    {
        $this->resourceId = $resourceId;
        return $this;
    }

    public function rotaId(string|null $rotaId): self
    {
        $this->rotaId = $rotaId;
        return $this;
    }

    public function startDateTime(string|null $startDateTime): self
    {
        $this->startDateTime = $startDateTime;
        return $this;
    }

    public function endDateTime(string|null $endDateTime): self
    {
        $this->endDateTime = $endDateTime;
        return $this;
    }

    public function manualSchedulingOnly(bool $isManual = true): self
    {
        $this->isManualSchedulingOnly = $isManual;
        return $this;
    }

    public function shiftType(string|null $shiftType): self
    {
        $this->shiftType = $shiftType;
        return $this;
    }

    public function description(string|null $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function arpObject(bool $isArpObject = true): self
    {
        $this->isArpObject = $isArpObject;
        return $this;
    }

    public function psoApiVersion(int $version): self
    {
        $this->psoApiVersion = $version;
        return $this;
    }

    public function build(): array
    {
        if (!$this->shiftId) {
            throw new InvalidArgumentException("shiftId is required");
        }
        if (!$this->resourceId) {
            throw new InvalidArgumentException("resourceId is required");
        }

        return [
            'id' => $this->shiftId,
            'manual_scheduling_only' => $this->isManualSchedulingOnly,
            'start_datetime' => $this->startDateTime,
            'end_datetime' => $this->endDateTime,
            'description' => $this->description,
            ...($this->shiftType
                ? [$this->isArpObject ? 'ram_shift_category_id' : 'shift_type_id' => $this->shiftType]
                : []),
            $this->isArpObject ? 'ram_rota_id' : 'rota_id' => $this->rotaId,
            $this->isArpObject ? 'ram_resource_id' : 'resource_id' => $this->resourceId,
        ];
    }
}

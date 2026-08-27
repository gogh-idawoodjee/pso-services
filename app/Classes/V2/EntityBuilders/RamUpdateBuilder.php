<?php

namespace App\Classes\V2\EntityBuilders;

use App\Enums\RamUpdateType;

class RamUpdateBuilder
{
    protected string $datasetId;

    protected RamUpdateType $updateType = RamUpdateType::CHANGE;

    protected bool $isMasterData = true;

    protected ?string $description = null;

    public function __construct(string $datasetId)
    {
        $this->datasetId = $datasetId;
    }

    public static function make(string $datasetId): self
    {
        return new self($datasetId);
    }

    public function updateType(RamUpdateType $updateType): self
    {
        $this->updateType = $updateType;

        return $this;
    }

    public function isMasterData(bool $isMasterData): self
    {
        $this->isMasterData = $isMasterData;

        return $this;
    }

    public function description(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function build(): array
    {
        return array_filter([
            'organisation_id' => config('pso-services.settings.organisation_id'),
            'dataset_id' => $this->datasetId,
            'user_id' => config('pso-services.settings.service_name'),
            'ram_update_type_id' => $this->updateType->value,
            'is_master_data' => $this->isMasterData,
            'description' => $this->description,
            'requesting_app_instance_id' => config('pso-services.settings.service_name'),
        ], static fn ($value) => $value !== null);
    }
}

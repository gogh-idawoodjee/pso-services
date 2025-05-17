<?php

namespace App\Classes\V2\EntityBuilders;


use App\Enums\InputMode;
use App\Enums\ProcessType;
use Illuminate\Support\Str;

class InputReferenceBuilder
{
    protected string $datasetId;
    protected InputMode $inputType = InputMode::LOAD;
    protected string|null $datetime = null;
    protected string|null $dseDuration = null;
    protected ProcessType|null $processType = null;
    protected string|null $appointmentWindow = null;
    protected string|null $id = null;
    protected string|null $description = null;
    protected int $psoApiVersion = 1;

    public function __construct(string $datasetId)
    {
        $this->datasetId = $datasetId;
    }

    public static function make(string $datasetId): self
    {
        return new self($datasetId);
    }

    public function inputType(InputMode $inputType): self
    {
        $this->inputType = $inputType;
        return $this;
    }

    public function datetime(string|null $datetime): self
    {
        $this->datetime = $datetime;
        return $this;
    }

    public function dseDuration(string|null $duration): self
    {
        $this->dseDuration = $duration;
        return $this;
    }

    public function processType(ProcessType|null $processType): self
    {
        $this->processType = $processType;
        return $this;
    }

    public function appointmentWindow(string|null $window): self
    {
        $this->appointmentWindow = $window;
        return $this;
    }

    public function id(string|null $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function description(string|null $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function psoApiVersion(int $version): self
    {
        $this->psoApiVersion = $version;
        return $this;
    }

    public function build(): array
    {
        return array_filter([
            'datetime' => $this->datetime ?? now()->toAtomString(),
            'id' => $this->id ?? Str::orderedUuid()->getHex()->toString(),
            'input_type' => $this->inputType->value,
            'organisation_id' => '2',
            'dataset_id' => $this->datasetId,
            'user_id' => config('pso-services.settings.service_name'),
            'duration' => $this->dseDuration,
            'description' => $this->description,
            'process_type' => $this->processType?->value,
            'appointment_window_duration' => $this->appointmentWindow,
        ], static fn($value) => $value !== null);
    }
}

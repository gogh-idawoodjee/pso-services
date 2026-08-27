<?php

namespace App\Classes\V2\EntityBuilders;

use App\Enums\BroadcastAllocationType;
use App\Enums\BroadcastPlanType;
use Illuminate\Support\Str;

class BroadcastBuilder
{
    protected ?int $broadcastAllocationType = null;

    /** @var BroadcastParameterBuilder[] */
    protected array $broadcastParameters = [];

    protected string $broadcastType = 'REST';

    protected bool $onceOnly = false;

    protected BroadcastPlanType $broadcastPlanType = BroadcastPlanType::COMPLETE;

    protected ?string $id = null;

    protected bool $active = true;

    protected ?string $description = null;

    protected ?float $minimumPlanQuality = null;

    protected ?int $minimumStepInterval = null;

    protected ?string $expiryDatetime = null;

    protected ?string $inputReferenceId = null;

    protected ?string $maximumFrequency = null;

    protected ?string $maximumWait = null;

    protected ?int $minimumVisitStatus = null;

    protected ?string $timeFilterStart = null;

    protected ?string $timeFilterEnd = null;

    public static function make(): static
    {
        return new static;
    }

    /**
     * @param  BroadcastAllocationType|BroadcastAllocationType[]  $types
     */
    public function allocationType(BroadcastAllocationType|array $types): static
    {
        $types = is_array($types) ? $types : [$types];
        $this->broadcastAllocationType = array_sum(array_map(static fn (BroadcastAllocationType $type) => $type->value, $types));

        return $this;
    }

    public function id(?string $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function active(bool $active): static
    {
        $this->active = $active;

        return $this;
    }

    public function description(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function minimumPlanQuality(?float $quality): static
    {
        $this->minimumPlanQuality = $quality;

        return $this;
    }

    public function minimumStepInterval(?int $interval): static
    {
        $this->minimumStepInterval = $interval;

        return $this;
    }

    public function expiryDatetime(?string $datetime): static
    {
        $this->expiryDatetime = $datetime;

        return $this;
    }

    public function inputReferenceId(?string $id): static
    {
        $this->inputReferenceId = $id;

        return $this;
    }

    public function maximumFrequency(?string $frequency): static
    {
        $this->maximumFrequency = $frequency;

        return $this;
    }

    public function maximumWait(?string $wait): static
    {
        $this->maximumWait = $wait;

        return $this;
    }

    public function minimumVisitStatus(?int $status): static
    {
        $this->minimumVisitStatus = $status;

        return $this;
    }

    public function timeFilterStart(?string $datetime): static
    {
        $this->timeFilterStart = $datetime;

        return $this;
    }

    public function timeFilterEnd(?string $datetime): static
    {
        $this->timeFilterEnd = $datetime;

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
        $broadcast_id = $this->id ?? Str::orderedUuid()->getHex()->toString();

        $parameters = array_map(
            static fn (BroadcastParameterBuilder $param) => $param->finalize($broadcast_id),
            $this->broadcastParameters,
        );

        $broadcast = array_filter([
            'active' => $this->active,
            'id' => $broadcast_id,
            'allocation_type' => $this->broadcastAllocationType,
            'broadcast_type_id' => $this->broadcastType,
            'once_only' => $this->onceOnly,
            'plan_type' => $this->broadcastPlanType->value,
            'description' => $this->description,
            'minimum_plan_quality' => $this->minimumPlanQuality,
            'minimum_step_interval' => $this->minimumStepInterval,
            'expiry_datetime' => $this->expiryDatetime,
            'input_reference_id' => $this->inputReferenceId,
            'maximum_frequency' => $this->maximumFrequency,
            'maximum_wait' => $this->maximumWait,
            'minimum_visit_status' => $this->minimumVisitStatus,
            'time_filter_start' => $this->timeFilterStart,
            'time_filter_end' => $this->timeFilterEnd,
        ], static fn ($value) => $value !== null);

        return [
            'Broadcast' => $broadcast,
            'Broadcast_Parameter' => $parameters,
        ];
    }
}

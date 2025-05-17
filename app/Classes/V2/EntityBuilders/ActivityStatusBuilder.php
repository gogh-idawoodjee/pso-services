<?php

namespace App\Classes\V2\EntityBuilders;

use App\Enums\ActivityStatus as ActivityStatusEnum;
use App\Helpers\PSOHelper;
use Carbon\Carbon;

class ActivityStatusBuilder
{
    protected string $activityId;
    protected ActivityStatusEnum $statusId;

    protected string|null $resourceId = null;
    protected bool|null $fixed = null;
    protected int|null $visitId = null;
    protected string|null $duration = null;
    protected string|null $dateTimeFixed = null;
    protected string|null $dateTimeEarliest = null;
    protected string|null $reason = null;
    protected string|null $timestampOverride = null;

    public static function make(string $activityId, ActivityStatusEnum $statusId): static
    {
        $instance = new static();
        $instance->activityId = $activityId;
        $instance->statusId = $statusId;
        return $instance;
    }

    public function resourceId(string|null $resourceId): static
    {
        $this->resourceId = $resourceId;
        return $this;
    }

    public function fixed(bool|null $fixed): static
    {
        $this->fixed = $fixed;
        return $this;
    }

    public function visitId(int|null $visitId): static
    {
        $this->visitId = $visitId;
        return $this;
    }

    public function duration(string|null $duration): static
    {
        $this->duration = $duration;
        return $this;
    }

    public function dateTimeFixed(string|null $dateTimeFixed): static
    {
        $this->dateTimeFixed = $dateTimeFixed;
        return $this;
    }

    public function dateTimeEarliest(string|null $dateTimeEarliest): static
    {
        $this->dateTimeEarliest = $dateTimeEarliest;
        return $this;
    }

    public function reason(string|null $reason): static
    {
        $this->reason = $reason;
        return $this;
    }

    public function timestampOverride(string|null $timestamp): static
    {
        $this->timestampOverride = $timestamp;
        return $this;
    }

    public function build(): array
    {
        $now = Carbon::now()->toAtomString();
        $overrideEnabled = config("pso-services.settings.override_commit_timestamps");
        $overrideValue = config("pso-services.settings.override_commit_timestamp_value");

        $dateTimeStatus = $this->timestampOverride ?? ($overrideEnabled ? $overrideValue : $now);
        $dateTimeStamp = $this->timestampOverride ?? ($overrideEnabled ? $overrideValue : $now);

        $durationFormatted = PSOHelper::setPSODuration($this->duration ?? "0");

        $visitId = $this->visitId ?? 1;

        $fixed = $this->fixed ?? !$this->isUnscheduledStatus();

        $status = [
            'activity_id' => $this->activityId,
            'status_id' => $this->statusId,
            'date_time_status' => $dateTimeStatus,
            'visit_id' => $visitId,
            'fixed' => $fixed,
            'date_time_stamp' => $dateTimeStamp,
            'reason' => $this->reason,
        ];

        if (!$this->isUnscheduledStatus()) {
            $status['resource_id'] = (string)$this->resourceId;

            if ($this->dateTimeFixed) {
                $status['date_time_fixed'] = $this->dateTimeFixed;
            }

            if ($this->dateTimeEarliest) {
                $status['date_time_earliest'] = $this->dateTimeEarliest;
            }
        }

        // optionally add 'duration' field
        // $status['duration'] = $durationFormatted;

        return $status;
    }

    protected function isUnscheduledStatus(): bool
    {
        return !in_array($this->statusId, [
            ActivityStatusEnum::IGNORE,
            ActivityStatusEnum::UNALLOCATED,
            ActivityStatusEnum::ALLOCATED,
        ], true);
    }
}

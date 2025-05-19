<?php

namespace App\Classes\V2\EntityBuilders;

use App\Enums\ActivityClass;
use App\Enums\ActivityStatus;
use App\Helpers\PSOHelper;
use App\Helpers\Stubs\Location;
use App\Helpers\Stubs\Region;
use App\Helpers\Stubs\Skill;
use App\Helpers\Stubs\Sla;

class ActivityBuilder
{
    protected array $data = [];
    protected bool $isAbRequest = false;
    protected int $psoApiVersion = 1;

    protected array $skills = [];
    protected array $regions = [];
    protected string|null $locality = null;

    protected ActivityClass $activityClass;
    protected ActivityStatusBuilder|null $customStatusBuilder = null;

    public static function make(array $activityData): self
    {
        $instance = new self();
        $instance->data = $activityData;
        $instance->activityClass = ActivityClass::CALL; // default here
        return $instance;
    }

    public function withActivityClass(ActivityClass $class): self
    {
        $this->activityClass = $class;
        return $this;
    }


    public function asAbRequest(bool $flag = true): self
    {
        $this->isAbRequest = $flag;
        return $this;
    }

    public function withPsoApiVersion(int $version): self
    {
        $this->psoApiVersion = $version;
        return $this;
    }

    public function withSkills(array $skills): self
    {
        $this->skills = $skills;
        return $this;
    }

    public function withRegions(array $regions): self
    {
        $this->regions = $regions;
        return $this;
    }

    public function withLocality(string|null $locality): self
    {
        $this->locality = $locality;
        return $this;
    }

    public function withActivityStatusBuilder(ActivityStatusBuilder $builder): self
    {

        $this->customStatusBuilder = $builder;
        return $this;
    }


    public function build(): array
    {
        $activityId = $this->isAbRequest
            ? data_get($this->data, 'data.activityId') . config('pso-services.defaults.activity.appointment_booking_suffix')
            : data_get($this->data, 'data.activityId');

        $description = $this->isAbRequest
            ? 'Appointment Request for: ' . data_get($this->data, 'data.activityId')
            : data_get($this->data, 'data.description');

        $hasLocation = $this->hasLocation();

        $activity = [
            'id' => $activityId,
            'activity_class_id' => $this->activityClass->value,
            'activity_type_id' => data_get($this->data, 'data.activityTypeId'),
            'priority' => data_get($this->data, 'data.priority') ?? config('pso-services.defaults.activity.priority'),
            'duration' => PSOHelper::setPSODuration(data_get($this->data, 'data.duration')),
            'description' => $description,
            'date_time_created' => now()->toAtomString(),
            'date_time_open' => now()->toAtomString(),
            'base_value' => data_get($this->data, 'data.baseValue') ?? config('pso-services.defaults.activity.base_value'),
            'split_allowed' => data_get($this->data, 'data.splitAllowed') ?? config('pso-services.defaults.activity.split_allowed'),
            'do_on_location_incentive' => config('pso-services.defaults.do_on_location_incentive'),
            'do_in_locality_incentive' => config('pso-services.defaults.do_in_locality_incentive'),
        ];

        if ($hasLocation) {
            $activity['location_id'] = $activityId;
        }

        $activitySkills = collect($this->skills ?: data_get($this->data, 'data.skills', []))
            ->map(static fn($skill) => Skill::make($skill, $activityId))
            ->values()
            ->toArray();

        $status = $this->resolveStatus($activityId);

        $result = [
            'Activity' => $activity,
            'Activity_Status' => $status

        ];

        if ($hasLocation) {
            $result['Location'] = Location::make(
                id: $activityId,
                latitude: data_get($this->data, 'data.lat'),
                longitude: data_get($this->data, 'data.long'),
                locality: $this->locality
            );

            $activityRegions = collect($this->regions ?: data_get($this->data, 'data.regions', []))
                ->map(static fn($regionId) => Region::make($regionId, $activityId))
                ->values()
                ->toArray();

            $result['Location_Region'] = $activityRegions;
        }

        // Only add SLA and Skill if activity class is not PRIVATE
        if ($this->activityClass !== ActivityClass::PRIVATE) {
            $result['Activity_SLA'] = Sla::make(
                activityId: $activityId,
                slaTypeId: data_get($this->data, 'data.slaTypeId'),
                datetimeStart: data_get($this->data, 'data.slaStart'),
                datetimeEnd: data_get($this->data, 'data.slaEnd')
            );

            $result['Activity_Skill'] = $activitySkills;
        }

        return $result;
    }


    private function hasLocation(): bool
    {
        $lat = data_get($this->data, 'data.lat');
        $long = data_get($this->data, 'data.long');

        return is_numeric($lat) && is_numeric($long);
    }


    private function resolveStatus(string $activityId): array
    {

        if ($this->isAbRequest) {
            return ActivityStatusBuilder::make($activityId, ActivityStatus::IGNORE)
                ->duration(data_get($this->data, 'data.duration'))
                ->visitId(1)
                ->build();
        }

        if ($this->customStatusBuilder) {
            return $this->customStatusBuilder->build();
        }


        return ActivityStatusBuilder::make($activityId, ActivityStatus::from(data_get($this->data, 'data.status')))
            ->resourceId(data_get($this->data, 'data.resourceId'))
            ->fixed(data_get($this->data, 'data.fixed'))
            ->duration(data_get($this->data, 'data.duration'))
            ->visitId(data_get($this->data, 'data.visitId') ?? 1)
            ->build();
    }


}

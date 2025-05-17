<?php

namespace App\Classes\V2\EntityBuilders;

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

    public static function make(array $activityData): self
    {
        $instance = new self();
        $instance->data = $activityData;
        return $instance;
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

    public function build(): array
    {
        $activityId = $this->isAbRequest
            ? data_get($this->data, 'data.activityId') . config('pso-services.defaults.activity.appointment_booking_suffix')
            : data_get($this->data, 'data.activityId');

        $description = $this->isAbRequest
            ? 'Appointment Request for: ' . data_get($this->data, 'data.activityId')
            : data_get($this->data, 'data.description');

        $activity = [
            'id' => $activityId,
            'activity_class_id' => 'CALL',
            'activity_type_id' => data_get($this->data, 'data.activityTypeId'),
            'location_id' => $activityId,
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

        $activitySkills = collect($this->skills ?: data_get($this->data, 'data.skills', []))
            ->map(static fn($skill) => Skill::make($skill, $activityId))
            ->values()
            ->toArray();

        $activityRegions = collect($this->regions ?: data_get($this->data, 'data.regions', []))
            ->map(static fn($regionId) => Region::make($regionId, $activityId))
            ->values()
            ->toArray();


        $status = $this->isAbRequest
            ? ActivityStatusBuilder::make($activityId, ActivityStatus::IGNORE)
                ->duration(data_get($this->data, 'data.duration'))
                ->visitId(1)
                ->build()

            : ActivityStatusBuilder::make($activityId, ActivityStatus::from(data_get($this->data, 'data.status')))
                ->resourceId(data_get($this->data, 'data.resourceId'))
                ->fixed(data_get($this->data, 'data.fixed'))
                ->duration(data_get($this->data, 'data.duration'))
                ->visitId(data_get($this->data, 'data.visitId') ?? 1)
                ->build();

        $location = Location::make(
            id: $activityId,
            latitude: data_get($this->data, 'data.lat'),
            longitude: data_get($this->data, 'data.long'),
            locality: $this->locality
        );

        $sla = Sla::make(
            activityId: $activityId,
            slaTypeId: data_get($this->data, 'data.slaTypeId'),
            datetimeStart: data_get($this->data, 'data.slaStart'),
            datetimeEnd: data_get($this->data, 'data.slaEnd')
        );

        return [
            'Activity' => $activity,
            'Activity_Status' => $status,
            'Activity_Skill' => $activitySkills,
            'Location' => $location,
            'Activity_SLA' => $sla,
            'Location_Region' => $activityRegions,
        ];
    }
}

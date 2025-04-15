<?php

namespace App\Helpers\Stubs;


use App\Helpers\PSOHelper;
use Carbon\Carbon;


class Activity
{
    public static function build(object $activityData, bool $isAbRequest = false): array

        // todo ensure that activityData is always sent as json
    {
        $activityId = $isAbRequest
            ? $activityData->activity_id . config('pso-services.defaults.activity.appointment_booking_suffix')
            : $activityData->activity_id;

        $activity = [
            'id' => $activityId,
            'activity_class_id' => 'CALL',
            'activity_type_id' => $activityData->activity_type_id,
            'location_id' => $activityId,
            'priority' => $activityData->priority ?? config('pso-services.defaults.activity.priority'),
            'duration' => PSOHelper::setPSODuration($activityData->duration),
            'description' => $activityData->description ?? 'Appointment Request',
            'date_time_created' => Carbon::now()->toAtomString(),
            'date_time_open' => Carbon::now()->toAtomString(),
            'base_value' => $activityData->base_value ?? config('pso-services.defaults.activity.base_value'),
            'split_allowed' => config('pso-services.defaults.activity.split_allowed'),
            'do_on_location_incentive' => config('pso-services.defaults.do_on_location_incentive'),
            'do_in_locality_incentive' => config('pso-services.defaults.do_in_locality_incentive'),
        ];

        $activitySkills = collect($activityData->skill ?? [])
            ->map(fn($skill) => Skill::make($skill, 'activity', $activityId))
            ->values()
            ->toArray();

        $activityRegions = collect($activityData->region ?? [])
            ->map(fn($regionId) => Region::make($regionId, 'activity', $activityId))
            ->values()
            ->toArray();

        $status = $isAbRequest
            ? ActivityStatus::make(
                activityId: $activityId,
                statusId: -1,
                visitId: 1,
                duration: $activityData->duration
            )
            : ActivityStatus::make(
                activityId: $activityId,
                statusId: $activityData->status_id,
                visitId: $activityData->visit_id ?? 1,
                duration: $activityData->duration,
                fixed: isset($activityData->fixed),
                resourceId: $activityData->resource_id ?? null
            );

        $locality = isset($activityData->region) && config('pso-services.settings.use_region_as_locality')
            ? $activityData->region[0]
            : null;

        $location = Location::make(
            id: $activityId,
            latitude: $activityData->lat,
            longitude: $activityData->long,
            locality: $locality
        );

        $sla = Sla::make(
            activityId: $activityId,
            slaTypeId: $activityData->sla_type_id,
            datetimeStart: $activityData->sla_start,
            datetimeEnd: $activityData->sla_end
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

<?php

namespace App\Helpers\Stubs;


use App\Enums\ActivityStatus;
use App\Helpers\PSOHelper;
use Carbon\Carbon;
use App\Helpers\Stubs\ActivityStatus as ActivityStatusStub;


class Activity
{
    public static function make(array $activityData, bool $isAbRequest = false, int $psoApiVersion = 1): array

        // todo ensure that activityData is always sent as json
    {
        $activityId = $isAbRequest

            ? data_get($activityData, 'data.activityId') . config('pso-services.defaults.activity.appointment_booking_suffix')
            : data_get($activityData, 'data.activityId');

        $description = $isAbRequest
            ? 'Appointment Request for: ' . data_get($activityData, 'data.activityId')
            : data_get($activityData, 'data.description');

        $activity = [
            'id' => $activityId,
            'activity_class_id' => 'CALL',
            'activity_type_id' => data_get($activityData, 'data.activityTypeId'),
            'location_id' => $activityId,
            'priority' => data_get($activityData, 'data.priority') ?? config('pso-services.defaults.activity.priority'),
            'duration' => PSOHelper::setPSODuration(data_get($activityData, 'data.duration')),
            'description' => $description,
            'date_time_created' => Carbon::now()->toAtomString(),
            'date_time_open' => Carbon::now()->toAtomString(),
            'base_value' => data_get($activityData, 'data.baseValue') ?? config('pso-services.defaults.activity.base_value'),
            'split_allowed' => data_get($activityData, 'data.splitAllowed') ?? config('pso-services.defaults.activity.split_allowed'),
            'do_on_location_incentive' => config('pso-services.defaults.do_on_location_incentive'),
            'do_in_locality_incentive' => config('pso-services.defaults.do_in_locality_incentive'),
        ];

        $activitySkills = collect(data_get($activityData, 'data.skills') ?? [])
            ->map(static fn($skill) => Skill::make($skill, $activityId))
            ->values()
            ->toArray();

        $activityRegions = collect(data_get($activityData, 'data.regions') ?? [])
            ->map(static fn($regionId) => Region::make($regionId, $activityId))
            ->values()
            ->toArray();

        $status = $isAbRequest
            ? ActivityStatusStub::make(
                activityId: $activityId,
                statusId: ActivityStatus::IGNORE,
                visitId: 1,
                duration: data_get($activityData, 'data.duration')
            )
            : ActivityStatusStub::make(
                activityId: $activityId,
                statusId: ActivityStatus::from(data_get($activityData, 'data.status')),
                resourceId: data_get($activityData, 'data.resourceId'),
                fixed: data_get($activityData, 'data.fixed') ?? false,
                visitId: data_get($activityData, 'data.visitId') ?? 1,
                duration: data_get($activityData, 'data.duration')
            );

        $locality = null; //todo revisit locality logic

        $location = Location::make(
            id: $activityId,
            latitude: data_get($activityData, 'data.lat'),
            longitude: data_get($activityData, 'data.long'),
            locality: $locality
        );

        $sla = Sla::make(
            activityId: $activityId,
            slaTypeId: data_get($activityData, 'data.slaTypeId'),
            datetimeStart: data_get($activityData, 'data.slaStart'),
            datetimeEnd: data_get($activityData, 'data.slaEnd')
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

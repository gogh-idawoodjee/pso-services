<?php

namespace App\Helpers\Stubs;

/**
 * Rewrites an appointment-request activity payload (built by ActivityBuilder::asAbRequest())
 * for real booking: the temporary AB-request activity is created with the suffixed
 * activity id (see ActivityBuilder::build()), and this strips that suffix from every
 * entity that referenced it, and overrides the SLA window with the accepted offer's times.
 *
 * The set of fields touched here is exhaustive against ActivityBuilder::build()'s output —
 * Activity.id/location_id, Activity_Status.activity_id, Location.id,
 * Location_Region[].location_id, Activity_SLA.activity_id, and Activity_Skill[].activity_id
 * are the only places that entity ever writes the (suffixed) activity id.
 */
class BookAppointmentActivity
{
    public static function finalize(array $activity, string $activityId, string $slaStart, string $slaEnd): array
    {
        if (isset($activity['Activity']['id'])) {
            $activity['Activity']['id'] = $activityId;
        }

        if (isset($activity['Activity']['location_id'])) {
            $activity['Activity']['location_id'] = $activityId;
        }

        if (isset($activity['Activity_Status']['activity_id'])) {
            $activity['Activity_Status']['activity_id'] = $activityId;
        }

        if (isset($activity['Location']['id'])) {
            $activity['Location']['id'] = $activityId;
        }

        if (! empty($activity['Location_Region'])) {
            $activity['Location_Region'] = array_map(
                static fn (array $row) => [...$row, 'location_id' => $activityId],
                $activity['Location_Region'],
            );
        }

        if (isset($activity['Activity_SLA'])) {
            $activity['Activity_SLA']['activity_id'] = $activityId;
            $activity['Activity_SLA']['datetime_start'] = $slaStart;
            $activity['Activity_SLA']['datetime_end'] = $slaEnd;
        }

        if (! empty($activity['Activity_Skill'])) {
            $activity['Activity_Skill'] = array_map(
                static fn (array $row) => [...$row, 'activity_id' => $activityId],
                $activity['Activity_Skill'],
            );
        }

        return $activity;
    }
}

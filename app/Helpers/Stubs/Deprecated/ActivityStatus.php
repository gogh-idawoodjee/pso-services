<?php

namespace App\Helpers\Stubs\Deprecated;


use App\Helpers\PSOHelper;
use Carbon\Carbon;

/**
 * @deprecated Use App\Builders\ActivityBuilder instead.
 * This class will be removed in an upcoming release.
 */
class ActivityStatus
{
    public static function make(
        string                    $activityId,
        \App\Enums\ActivityStatus $statusId,
        string|null               $resourceId = null,
        bool|null                 $fixed = null,
        int|null                  $visitId = null,
        string|null               $duration = null,
        string|null               $dateTimeFixed = null,
        string|null               $dateTimeEarliest = null,
        string|null               $reason = null,
        string|null               $timestampOverride = null,
        int                       $psoApiVersion = 1
    ): array
    {
        $fixed ??= false;
        $duration ??= "0";
        $visitId ??= 1;
        $now = Carbon::now()->toAtomString();
        $overrideEnabled = config("pso-services.settings.override_commit_timestamps");
        $overrideValue = config("pso-services.settings.override_commit_timestamp_value");

        $dateTimeStatus = $overrideEnabled ? $overrideValue : $now;
        $dateTimeStamp = $overrideEnabled ? $overrideValue : $now;

        $isFixed = $fixed ?: (!self::isUnscheduledStatuses($statusId));
        $durationFormatted = PSOHelper::setPSODuration($duration);

        $status = [
            'activity_id' => $activityId,
            'status_id' => $statusId,
            'date_time_status' => $dateTimeStatus,
            'visit_id' => $visitId ?: 1,
            'fixed' => $isFixed,
            'date_time_stamp' => $dateTimeStamp,
            'reason' => $reason,
            // You can optionally include 'duration' here if needed
            // 'duration' => $durationFormatted,
        ];

        if (!self::isUnscheduledStatuses($statusId)) {
            $status['resource_id'] = (string)$resourceId;

            if ($dateTimeFixed) {
                $status['date_time_fixed'] = $dateTimeFixed;
            }

            if ($dateTimeEarliest) {
                $status['date_time_earliest'] = $dateTimeEarliest;
            }
        }

        return $status;
    }

    private static function isUnscheduledStatuses($statusId): bool
    {

        return !($statusId !== \App\Enums\ActivityStatus::IGNORE && $statusId !== \App\Enums\ActivityStatus::UNALLOCATED && $statusId !== \App\Enums\ActivityStatus::ALLOCATED);

    }
}

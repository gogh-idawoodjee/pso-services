<?php

namespace App\Helpers\Stubs;

use App\Helpers\PSOHelper;
use Carbon\Carbon;

class ActivityStatus
{
    public static function make(
        string      $activityId,
        int         $statusId,
        int|null    $visitId = null,
        string|null $duration = null,
        bool|null   $fixed = null,
        string|null $resourceId = null,
        string|null $reason = null,
        string|null $dateTimeFixed = null,
        string|null $dateTimeEarliest = null,
        string|null $timestampOverride = null,
        int         $psoApiVersion = 1
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

        $isFixed = $fixed ?: ($statusId !== -1 && $statusId !== 0);
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

        if ($statusId !== -1 && $statusId !== 0) {
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
}

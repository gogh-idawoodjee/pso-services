<?php

namespace App\Helpers;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;

class PSOHelper
{

    public static function setTimeZone($time_zone, $check_from_source = false, $source_collection = null): string|Stringable|null
    {
        if ($time_zone) {
            $sign = $time_zone < 0 ? '-' : '+';
            return sprintf('%s%02d:00', $sign, abs($time_zone));
        }

        if ($check_from_source && $source_collection?->first()) {
            return Str::of($source_collection->first()['activity_start'])->substr(20, 6);
        }

        return null;
    }


    /**
     * @param $duration //in minutes
     * @return string
     */
    public static function setPSODuration($duration): string
    {
        $duration = (int)$duration;
        $hours = intdiv($duration, 60);
        $minutes = $duration % 60;

        $result = 'PT';
        if ($hours > 0) {
            $result .= $hours . 'H';
        }
        if ($minutes > 0) {
            $result .= $minutes . 'M';
        }

        // If both hours and minutes are zero, return 'PT0M' as a fallback
        if ($result !== 'PT') {
            return $result;
        }
        return 'PT0M';
    }


    public static function setPSODurationDays($duration): string
    {
        return 'P' . $duration . 'D';
    }

    public static function RotaID($dataset_id, $rota_id)
    {
        return $rota_id ?: $dataset_id;
    }

    public static function GetTimeOut()
    {
        if (config('pso-services.debug.debug_mode_on')) {
            return config('pso-services.debug.debug_timeout');
        }
        return config('pso-services.defaults.timeout');
    }

    public static function toIso8601(mixed $datetime): string
    {
        if (!$datetime instanceof Carbon) {
            $datetime = Carbon::parse($datetime);
        }

        return $datetime->format('Y-m-d\TH:i:s');
    }

}

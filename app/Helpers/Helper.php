<?php // Code within app\Helpers\Helper.php

namespace App\Helpers;

use Illuminate\Support\Str;

class Helper
{

    public static function setTimeZone($time_zone, $check_from_source = false, $source_collection = null)
    {
        $tz = null;
        if ($time_zone) {
            $tz = '+' . $time_zone . ':00';
            if ($time_zone < 10 && $time_zone > -10) {
                $tz = $time_zone < 0 ? '-0' . abs($time_zone) . ':00' : '+0' . abs($time_zone) . ':00';
            }
        }
        if (!$time_zone && $check_from_source) {
            $tz = Str::of($source_collection->first()['activity_start'])->substr(20, 6);
        }

        return $tz;
    }

    public static function setPSODuration($duration)
    {
        return 'PT' . intdiv(($duration), 60) . 'H' . (($duration) % 60) . 'M';
    }

    public static function setPSODurationDays($duration)
    {
        return 'P' . $duration . 'D';
    }
}

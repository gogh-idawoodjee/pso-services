<?php // Code within app\Helpers\Helper.php

namespace App\Helpers;

class Helper
{
    public static function shout(string $string)
    {
        return strtoupper($string);
    }

    public static function setTimeZone($time_zone)
    {
        $tz = null;
        if ($time_zone) {
            $tz = '+' . $time_zone . ':00';
            if ($time_zone < 10 && $time_zone > -10) {
                $tz = $time_zone < 0 ? '-0' . abs($time_zone) . ':00' : '+0' . abs($time_zone) . ':00';
            }
        }

        return $tz;
    }

    public static function setPSODuration($duration)
    {
        return 'PT' . intdiv(($duration), 60) . 'H' . (($duration) % 60) . 'M';
    }
}

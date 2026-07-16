<?php

namespace App\Helpers\Stubs;


use App\Helpers\PSOHelper;
use Carbon\Carbon;

class RamUnavailability
{
    public static function make(

        string $timePatternId,
        string $baseDateTime,
        int    $duration,
    ): array
    {

        return [
            'id' => $timePatternId,
            'base_time' => Carbon::parse($baseDateTime)->toAtomString(),
            'duration' => PSOHelper::setPSODuration($duration)
        ];


    }
}

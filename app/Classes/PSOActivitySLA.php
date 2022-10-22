<?php

namespace App\Classes;

use Illuminate\Support\Facades\Date;

class PSOActivitySLA extends Activity
{


    private string $sla_type_id;
    private string $datetime_start;
    private string $datetime_end;
    private int $priority;
    private bool $start_based;

    public function __construct($sla_type_id, $datetime_start, $datetime_end, $priority = 2, $start_based = true)
    {
        $this->priority = $priority ?? config('ifs.app_params.default_sla_priority');
        $this->sla_type_id = $sla_type_id;
        $this->datetime_start = $datetime_start;
        $this->datetime_end = $datetime_end;
        $this->start_based = $start_based;

        return $this;

    }

    public function toJson($activity_id)
    {
        return [
            'activity_id' => $activity_id,
            'sla_type_id' => $this->sla_type_id,
            'datetime_start' => $this->datetime_start,
            'datetime_end' => $this->datetime_end,
            'priority' => $this->priority,
            'start_based' => $this->start_based
        ];
    }

}

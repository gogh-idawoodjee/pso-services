<?php

namespace App\Classes;

use Carbon\Carbon;

class PSOActivityStatus extends Activity
{
    private int $status_id;
    private string $date_time_status;
    private int $visit_id;
    private bool $fixed;
    private string $date_time_stamp;
    private string $duration;
    private string $resource_id;


    public function __construct($status_id, $visit_id, $duration, $fixed = false, $resource_id = '')
    {
        $this->status_id = $status_id;
        $this->date_time_status = Carbon::now()->toAtomString();
        $this->date_time_stamp = Carbon::now()->toAtomString();
        $this->visit_id = $visit_id;
        $this->fixed = $fixed;
        $this->resource_id = $resource_id;
        $this->duration = 'PT' . intdiv(($duration), 60) . 'H' . (($duration) % 60) . 'M';
    }

    public function toJson($activity_id)
    {
        return [
            'activity_id' => $activity_id,
            'status_id' => $this->status_id,
            'date_time_status' => $this->date_time_status,
            'visit_id' => $this->visit_id,
            'fixed' => $this->fixed,
            'date_time_stamp' => $this->date_time_stamp,
            'duration' => $this->duration
        ];
    }

}

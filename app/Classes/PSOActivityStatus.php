<?php

namespace App\Classes;

use App\Helpers\PSOHelper;
use Carbon\Carbon;
use Illuminate\Support\Arr;

class PSOActivityStatus extends Activity
{
    private int $status_id;
    private string|null $date_time_status;
    private string|null $date_time_fixed;
    private string|null $date_time_earliest;
    private int $visit_id;
    private bool $fixed;
    private string $date_time_stamp;
    private string $duration;
    private string|null $resource_id;
    private string|null $reason;

    public function __construct($status_id, $visit_id, $duration = "0", $fixed = false, $resource_id = null, $reason = null, $date_time_fixed = null, $date_time_earliest = null, $timestamp_override = null)
    {
        $this->status_id = $status_id;

        $this->date_time_status = $timestamp_override;
        $this->date_time_stamp = Carbon::now()->toAtomString();

        if (!config("pso-services.settings.override_commit_timestamps")) {
            $this->date_time_status = Carbon::now()->toAtomString();

        }
        $this->visit_id = $visit_id ?: 1;
        $this->resource_id = $resource_id;
        $this->duration = PSOHelper::setPSODuration($duration);
        $this->fixed = $fixed ?: ($status_id !== -1 && $status_id !== 0);
        $this->date_time_fixed = $date_time_fixed;
        $this->date_time_earliest = $date_time_earliest;
        $this->reason = $reason;
    }

    public function toJson($activity_id)
    {
        $status_json =
            [
                'activity_id' => $activity_id,
                'status_id' => $this->status_id,
                'date_time_status' => $this->date_time_status,
                'visit_id' => $this->visit_id,
                'fixed' => $this->fixed,
                'date_time_stamp' => $this->date_time_stamp,
                'duration' => $this->duration,
                'reason' => $this->reason
            ];

        if ($this->status_id !== -1 && $this->status_id !== 0) {
            $status_json = Arr::add($status_json, 'resource_id', (string)$this->resource_id);
            if ($this->date_time_fixed) {
                $status_json = Arr::add($status_json, 'date_time_fixed', $this->date_time_fixed);
            }
            if ($this->date_time_earliest) {
                $status_json = Arr::add($status_json, 'date_time_earliest', $this->date_time_earliest);
            }
        }

        return $status_json;
    }

}

<?php

namespace App\Classes;

use Carbon\Carbon;

class PSOActivity extends Activity
{

    private string $activity_class_id;
    private string $activity_type_id;
    private int $priority;
    private bool $split_allowed;
    private string $description;
    private string $date_time_created;
    private string $date_time_open;
    private int $base_value;
    private bool $interrupt;
    private array $activity_skill;
    private array $activity_region;
    private array $activity_sla;
    private PSOLocation $activity_location;
    private PSOActivityStatus $activity_status;


    public function __construct($activity_data, $is_ab_request = false)
    {

        $this->activity_id = $is_ab_request ? $activity_data->task_id . '_appt' : $activity_data->task_id;
        $this->activity_class_id = 'CALL';
        $this->activity_type_id = $activity_data->task_type;
        $this->priority = 100;
        $this->split_allowed = json_encode($activity_data->split_allowed);
        $this->description = $activity_data->description;
        $this->date_time_created = Carbon::now()->toAtomString();
        $this->date_time_open = Carbon::now()->toAtomString();
        $this->base_value = $activity_data->schedule_value;
        $this->interrupt = json_encode($activity_data->interrupt);
    }

    public static function create(...$params)
    {
        return new static(...$params);
    }

    public function addActivitySkill(PSOActivitySkill $skill)
    {
        $this->activity_skill[] = $skill;
        return $this;
    }

    public function addActivitySLA(PSOActivitySLA $sla)
    {
        $this->activity_sla[] = $sla;
        return $this;
    }

    public function addActivityRegion(PSOActivityRegion $region)
    {
        $this->activity_region[] = $region;
        return $this;

    }

    public function setActivityLocation(PSOLocation $location)
    {
        $this->activity_location = $location;
        return $this;
    }

    public function setActivityStatus(PSOActivityStatus $status)
    {
        $this->activity_status = $status;
        return $this;
    }

    public function ActivityLocation()
    {
        return $this->activity_location->toJson($this->activity_id);

    }

    public function ActivityStatus()
    {
        return $this->activity_status->toJson($this->activity_id);
    }

    public function ActivitySLAs()
    {
        return $this->ActivityDataToJson($this->activity_sla);

    }

    public function ActivitySkills()
    {
        clock()->info('calling method on Activity');

        return $this->ActivityDataToJson($this->activity_skill);
    }

    public function ActivityRegions()
    {
        return $this->ActivityDataToJson($this->activity_region);

    }

    public function ActivityToJson()
    {
        return [
            'id' => $this->activity_id,
            'activity_class_id' => $this->activity_class_id,
            'activity_type_id' => "$this->activity_type_id",
            'location_id' => $this->activity_id,
            'priority' => $this->priority,
            'split_allowed' => $this->split_allowed,
            'description' => "$this->description",
            'date_time_created' => $this->date_time_created,
            'date_time_open' => $this->date_time_open,
            'base_value' => $this->base_value,
            'interrupt' => $this->interrupt
        ];
    }

    // DRY method
    private function ActivityDataToJson($activity_data)
    {

        $data_json = [];
        foreach ($activity_data as $data) {
            $data_json = $data->toJson($this->activity_id);
        }
        return $data_json;
    }

}

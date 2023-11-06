<?php

namespace App\Classes;

use Carbon\Carbon;


class PSOActivity extends Activity
{

    private string $activity_class_id;
    private string $activity_type_id;
    private int $priority;
    private bool $fixed;
    private string $description;
    private string $date_time_created;
    private string $date_time_open;
    private int $base_value;
    private array $activity_skill = [];
    private array $activity_region = [];
    private array $activity_sla;
    private PSOLocation $activity_location;
    private PSOActivityStatus $activity_status;
    private bool $split_allowed;
    public $visit_id;
    public string|null $resource_id;


    public function __construct($activity_data, $is_ab_request = false)
    {

        $this->activity_id = $is_ab_request ? $activity_data->activity_id . config('pso-services.defaults.activity.appointment_booking_suffix') : $activity_data->activity_id;
        $this->activity_class_id = 'CALL';
        // todo make this an input in the API
        $this->split_allowed = config('pso-services.defaults.activity.split_allowed');

        $this->activity_type_id = $activity_data->activity_type_id;
        $this->priority = $activity_data->priority ?: config('pso-services.defaults.activity.priority');
        $this->description = isset($activity_data->description) ?: 'Appointment Request';
        $this->date_time_created = Carbon::now()->toAtomString();
        $this->date_time_open = Carbon::now()->toAtomString();
        $this->base_value = $activity_data->base_value ?: config('pso-services.defaults.activity.base_value');
        $this->fixed = (bool)isset($activity_data->fixed);
        $this->visit_id = isset($activity_data->visit_id) ?: 1;
        $this->resource_id = isset($activity_data->resource_id) ?: null;


        // build the skills
        if (isset($activity_data->skill)) {
            foreach ($activity_data->skill as $skill) {
                $this->addActivitySkill(new PSOSkill($skill));
            }
        }

        // build the regions
        if (isset($activity_data->region)) {
            foreach ($activity_data->region as $region) {
                $this->addActivityRegion(new PSORegion($region));
            }
        }

        // build the status
        if ($is_ab_request) {
            $this->activity_status = new PSOActivityStatus(-1, 1, $activity_data->duration);
        } else {
            $this->activity_status = new PSOActivityStatus($activity_data->status_id, $this->visit_id ?: 1, $activity_data->duration, $this->fixed, $this->resource_id);
        }

        // build the location
        if (isset($activity_data->region) && config('pso-services.settings.use_region_as_locality')) {
            $locality = $activity_data->region[0];
        } else {
            $locality = "";
        }
        $this->setActivityLocation(new PSOLocation($activity_data->lat, $activity_data->long, $locality));

        $this->addActivitySLA(new PSOActivitySLA($activity_data->sla_type_id, $activity_data->sla_start, $activity_data->sla_end));

    }


    public function FullActivityObject()
    {

        return [
            'Activity' => $this->ActivityToJson(),
            'Activity_Status' => $this->ActivityStatus(),
            'Activity_Skill' => $this->ActivitySkills(),
            'Location' => $this->ActivityLocation(),
            'Activity_SLA' => $this->ActivitySLAs(),
            'Location_Region' => $this->activity_region
        ];
    }

    public function addActivitySkill(PSOSkill $skill)
    {
        $this->activity_skill[] = $skill->toJson($this->activity_id);
        return $this;
    }

    public function addActivitySLA(PSOActivitySLA $sla)
    {
        $this->activity_sla[] = $sla;
        return $this;
    }

    public function addActivityRegion(PSORegion $region)
    {
        $this->activity_region[] = $region->toJson($this->activity_id);
        return $this;

    }

    public function setActivityLocation(PSOLocation $location)
    {
        $this->activity_location = $location;
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
        return $this->activity_skill;
    }


    public function ActivityToJson()
    {
        return [
            'id' => $this->activity_id,
            'activity_class_id' => $this->activity_class_id,
            'activity_type_id' => $this->activity_type_id,
            'location_id' => $this->activity_id,
            'priority' => $this->priority,
            'description' => (string)$this->description,
            'date_time_created' => $this->date_time_created,
            'date_time_open' => $this->date_time_open,
            'base_value' => $this->base_value,
            'split_allowed' => $this->split_allowed,
            'do_on_location_incentive' => config('pso-services.defaults.do_on_location_incentive'),
            'do_in_locality_incentive' => config('pso-services.defaults.do_in_locality_incentive')
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

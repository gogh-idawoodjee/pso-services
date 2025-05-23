<?php

namespace App\Classes\V1;

use Illuminate\Support\Str;

class PSOResource
{

    private string $resource_class_id;
    private string $resource_type_id;
    private string $surname;
    private string $first_name;
    private string $resource_id;


    private array $resource_skill = [];
    private array $resource_region = [];

    private PSOLocation $resource_location;
    private float $lat;
    private float $long;

    public function __construct($resource_data, $lat, $long)
    {

        $this->first_name = $resource_data->first_name;
        $this->surname = $resource_data->surname;
        $this->resource_id = $resource_data->resource_id ?? Str::upper($resource_data->first_name . $resource_data->surname);  // use the ID if it's sent, otherwise default it to first+last
        $this->resource_class_id = config('pso-services.defaults.resource.class_id');

        $this->lat = $lat;
        $this->long = $long;

        $this->resource_type_id = $resource_data->resource_type_id;

        // build the skills
        if ($resource_data->skill) {

            foreach ($resource_data->skill as $skill) {
                $this->addResourceSkill(new PSOSkill($skill));
            }
        }

        // build the regions
        if ($resource_data->region) {
            foreach ($resource_data->region as $region) {
                $this->addResourceRegion(new PSORegion($region, "resource"));
            }
        }

        // build the location
        $this->setResourceLocation(new PSOLocation($this->lat, $this->long));


    }

    public function FullResourceObject()
    {
        return [
            'RAM_resource' => $this->ResourceToJson(),
            'Location' => $this->ResourceLocation(),
            'RAM_Resource_Division' => $this->resource_region,
            'RAM_Resource_Skill' => $this->resource_skill
        ];
    }

    public function addResourceSkill(PSOSkill $skill)
    {
        $this->resource_skill[] = $skill->toJson($this->resource_id);
        return $this;
    }


    public function addResourceRegion(PSORegion $region)
    {
        $this->resource_region[] = $region->toJson($this->resource_id);
        return $this;

    }

    public function setResourceLocation(PSOLocation $location)
    {
        $this->resource_location = $location;
        return $this;
    }


    public function ResourceLocation()
    {
        return $this->resource_location->toJson($this->resource_id);
    }

    public function ResourceSkills()
    {
        return $this->resource_skill;
    }

    public function ResourceRegion()
    {
        return $this->resource_region;
    }

    public function ResourceToJson()
    {
        return [
            'id' => $this->resource_id,
            'ram_resource_class_id' => $this->resource_class_id,
            'ram_resource_type_id' => $this->resource_type_id,
            'ram_location_id_start' => $this->resource_id,
            'ram_location_id_end' => $this->resource_id,
            'first_name' => $this->first_name,
            'surname' => $this->surname
        ];
    }

    // DRY method
    private function ResourceDataToJson($activity_data)
    {
        $data_json = [];
        foreach ($activity_data as $data) {
            $data_json = $data->toJson($this->resource_id);
        }
        return $data_json;
    }

}

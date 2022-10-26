<?php

namespace App\Services;

use App\Classes\FSMResource;
use Illuminate\Support\Facades\Http;
use stdClass;


class IFSResourceService
{
    private string $fsm_token;

    private string $fsm_env;
    private string $fsm_url;
    private string $fsm_path;
    private string $resource_id;
    private string $fsm_query_string;
    private string $fsm_full_url;
    private stdClass $fsm_resource;


    public function __construct($token, $resource_id)
    {
        $this->fsm_token = $token;
        $this->resource_id = $resource_id;
        $this->fsm_env = config('ifs.fsm.fsm_environment');
        $this->fsm_path = config('ifs.fsm.fsm_resource_endpoint_path');
        $this->fsm_url = config('ifs.fsm.' . $this->fsm_env . '.base_url');
        $this->fsm_query_string = config('ifs.fsm.fsm_resource_endpoint_query_string');
        $this->fsm_full_url = 'https://' . $this->fsm_url . $this->fsm_path . $this->resource_id . $this->fsm_query_string;
        $this->fsm_resource = $this->getFSMResource($resource_id);

    }

    public static function createResource(...$params)
    {
        return new static(...$params);
    }

    private function getFSMResource($resource_id)
    {

        return $fsm_request = Http::withHeaders(['Authorization' => $this->fsm_token])->get($this->fsm_full_url)->object() ? Http::withHeaders(['Authorization' => $this->fsm_token])->get($this->fsm_full_url)->object() : new stdClass();
        // do later
        return new FSMResource($fsm_request, $resource_id);

    }

    public function getFSMResourceName(): string
    {
        if (get_object_vars($this->fsm_resource)) {
            return $this->fsm_resource->ResourceName();
        } else {
            return 'Fake Name';
        }
    }

    public function getFSMResourceID(): string
    {

        if (get_object_vars($this->fsm_resource)) {
            return $this->fsm_resource->getResourceID();
        } else {
            return 'Fake ID';
        }

    }
}

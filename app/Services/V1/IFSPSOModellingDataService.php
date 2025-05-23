<?php

namespace App\Services\V1;

use App\Classes\V1\PSORegion;
use App\Classes\V1\PSORegionType;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class IFSPSOModellingDataService extends IFSService
{

    private IFSPSOAssistService $IFSPSOAssistService;

    public function __construct($base_url, $token, $username, $password, $account_id = null, $requires_auth = false, $pso_environment = null)
    {
        parent::__construct($base_url, $token, $username, $password, $account_id, $requires_auth, $pso_environment);
        $this->IFSPSOAssistService = new IFSPSOAssistService($base_url, $token, $username, $password, $account_id, $requires_auth);

    }

    public function createDivision(Request $request)
    {

        $descriptions = (count($request->description ?: []) === count($request->region)) ? $request->description : null;

        foreach ($request->region as $key => $division) {
            $division = new PSORegion(
                $division,
                'RAM_Division',
                $descriptions ? $descriptions[$key] : null,
                $request->send ?: true
                //,
                //$request->region_parent
            );
            if ($request->region_parent) {
                // since this can't be null, let's not set it unless it's there
                $division->setParentDivision($request->region_parent);
            }
            if ($request->region_category) {
                // since this can't be null, let's not set it unless it's there
                $division->setDivisionType($request->region_category);
            }
            $divisions[] = $division->RAMtoJson();
        }


        $desc = 'add ' . count($request->region) . ' regions to ARP';
        $ram_update_payload = $this->IFSPSOAssistService->RAMUpdatePayload($request->dataset_id, $desc);

        $full_payload =
            [

                '@xmlns' => 'http://360Scheduling.com/Schema/DsModelling.xsd',
                'RAM_Update' => $ram_update_payload,
                'RAM_Division' => $divisions

            ];

        if ($request->region_category) {
            $division_type = new PSORegionType(
                $request->region_category
            );
            $full_payload = Arr::add($full_payload, 'RAM_Division_Type', $division_type->RAMtoJson());
        }


        return $this->IFSPSOAssistService->processPayload(
            $request->send_to_pso,
            ['DsModelling' => [$full_payload]],
            $this->token,
            $request->base_url,
            $desc,
            true,
            $request->dataset_id,
            $request->dataset_id
        );

    }

}

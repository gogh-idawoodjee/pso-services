<?php

namespace App\Services;

use App\Classes\PSORegion;
use Illuminate\Http\Request;

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

//        return $request;

        foreach ($request->region as $division) {
            $division = new PSORegion(
                $division,
                'RAM_Division',
                $request->description,
                $request->send ?: true,
                $request->region_parent,
                $request->region_category
            );
            $divisions[]=$division->RAMtoJson();
        }

        return $divisions;
    }

}

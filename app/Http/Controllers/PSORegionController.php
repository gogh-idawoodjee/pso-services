<?php

namespace App\Http\Controllers;

use App\Helpers\PSOHelper;
use App\Services\IFSPSOActivityService;
use App\Services\IFSPSOModellingDataService;
use Illuminate\Http\Request;

class PSORegionController extends Controller
{


    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     */
    public function store(Request $request)
    {
        //
        $request->validate([
            'send_to_pso' => 'boolean',
            'base_url' => ['url', 'required_if:send_to_pso,true', 'not_regex:/prod|prd/i'],
            'dataset_id' => 'string|required',
            'account_id' => 'string|required_if:send_to_pso,true',
            'description' => 'array',
            'region_parent' => 'string',
            'region_category' => 'string',
            'send' => 'boolean',
            'token' => 'string',
            'region' => 'array|required',
            'username' => 'string',
            'password' => 'string'
        ]);


        PSOHelper::ValidateSendToPSO($request);

        $region = new IFSPSOModellingDataService($request->base_url, $request->token, $request->username, $request->password, $request->account_id, $request->send_to_pso);

        if (!$region->isAuthenticated() && $request->send_to_pso) {
            return response()->json([
                'status' => 401,
                'description' => 'did not pass auth'
            ]);
        }

        return $region->createDivision($request);
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}

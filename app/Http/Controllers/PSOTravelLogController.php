<?php

namespace App\Http\Controllers;

use App\Helpers\PSOHelper;
use App\Models\PSOTravelLog;
use App\Services\IFSPSOTravelService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PSOTravelLogController extends Controller
{


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //

        $request->validate([
            'send_to_pso' => 'boolean',
            'base_url' => ['url', 'required_if:send_to_pso,true', 'not_regex:/prod|prd/i'],
            'dataset_id' => 'string|required',
            'account_id' => 'string|required_if:send_to_pso,true',
            'token' => 'string',
            'username' => 'string',
            'password' => 'string',
            'lat_from' => 'numeric|between:-90,90|required',
            'long_from' => 'numeric|between:-180,180|required',
            'lat_to' => 'numeric|between:-90,90|required',
            'long_to' => 'numeric|between:-180,180|required'
        ]);


        PSOHelper::ValidateSendToPSO($request);

        $travel = new IFSPSOTravelService($request->base_url, $request->token, $request->username, $request->password, $request->account_id, $request->send_to_pso);


        if ($request->send_to_pso && !$travel->isAuthenticated()) {
            return PSOHelper::notAuth();
        }


        return $travel->analyzetravel($request);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        // no creds for this one for now
        // receive the broadcast from PSO
        // process it
        // tell PSO it's all good in the hood
        $travel = new IFSPSOTravelService(null, null, null, null, null);
        $travel->receivePSOBroadcast($request);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}

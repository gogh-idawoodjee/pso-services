<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Services\IFSPSOResourceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PSOResourceShiftController extends Controller
{
    /**
     * this will probably an API call to return all shifts with a choice of raw or formatted.
     *
     * @return void
     */
    public function index()
    {
        //
    }


    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param $resource_id
     * @return JsonResponse
     * @throws ValidationException
     */
    public function update(Request $request, $resource_id)//: JsonResponse
    {
        // this must always be true
        $request->send_to_pso = true;

        $request->validate([
            'shift_id' => 'required|alpha_dash',
            'dataset_id' => 'required|string',
            'rota_id' => 'required|string',
            'token' => 'string',
            'shift_type' => 'required|string',
            'turn_manual_scheduling_on' => 'required|boolean',
            'send_to_pso' => 'boolean',
            'base_url' => ['url', 'required', 'not_regex:/prod|prd/i'],
            'account_id' => 'string|required',
            'username' => 'string',
            'password' => 'string'
        ]);


        // auth and validation is required because there is a GET request done first on the resource

        Helper::ValidateSendToPSO($request);

        $resource_init = new IFSPSOResourceService($request->base_url, $request->token, $request->username, $request->password, $request->account_id, $request->send_to_pso);

        if (!$resource_init->isAuthenticated()) {
            return response()->json([
                'status' => 401,
                'description' => 'did not pass auth'
            ]);

        }
        // do we need this? seems like we do, it initializes $this->pso_resource
        // // technically we could do this from the method
        $resource_init->getResource($resource_id, $request->dataset_id, $request->base_url);

        // send all that back to the service and let it do the work
        return $resource_init->setManualScheduling($request, $resource_id);

    }

}

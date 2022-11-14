<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Services\IFSPSOActivityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use Illuminate\Validation\ValidationException;

class PSOActivitySLAController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return void
     */
    public function store(Request $request)
    {
        //

    }


    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return void
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @param $activity_id
     * @return JsonResponse
     * @throws ValidationException
     */
    public function destroy(Request $request, $activity_id)//: JsonResponse
    {
        $request->merge(['activity_id' => $activity_id]);

        $request->validate([
            'send_to_pso' => 'boolean',
            'base_url' => ['url', 'required_if:send_to_pso,true', 'not_regex:/prod|prd/i'],
            'dataset_id' => 'string',
            'account_id' => 'string|required_if:send_to_pso,true',
            'token' => 'string',
            'username' => 'string',
            'password' => 'string',
            'activity_id' => 'string|required',
            'sla_type_id' => 'string|required',
            'priority' => 'numeric',
            'start_based' => 'boolean'
        ]);

        Helper::ValidateSendToPSO($request);

        $activity = new IFSPSOActivityService($request->base_url, $request->token, $request->username, $request->password, $request->account_id, $request->send_to_pso);

        if (!$activity->isAuthenticated() && $request->send_to_pso) {
            return response()->json([
                'status' => 401,
                'description' => 'did not pass auth'
            ]);
        }

        return $activity->deleteSLA($request);
    }
}

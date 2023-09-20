<?php

namespace App\Http\Controllers;

use App\Helpers\PSOHelper;
use App\Services\IFSPSOActivityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use JsonException;

class PSOActivityController extends Controller
{

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException|JsonException
     */
    public function store(Request $request)
    {
        $request->validate([
            'activity_id' => 'string',
            'activity_type_id' => 'string|required',
            'time_zone' => 'numeric|between:-24,24', // now made optional
            'relative_day' => 'integer|gt:-1',
            'priority' => 'integer',
            'relative_day_end' => 'integer|gt:-1|gt:relative_day|nullable|prohibits:window_size',
            'duration' => 'integer|gt:0|required',
            'window_size' => 'integer|in:0,3,4|nullable|prohibits:relative_day_end',
            'send_to_pso' => 'boolean',
            'lat' => 'numeric|between:-90,90|required',
            'long' => 'numeric|between:-180,180|required',
            'base_url' => ['url', 'required_if:send_to_pso,true', 'not_regex:/prod|prd/i'],
            'dataset_id' => 'string|required',
            'description' => 'string',
            'account_id' => 'string|required_if:send_to_pso,true',
            'token' => 'string',
            'username' => 'string',
            'password' => 'string'
        ]);

        PSOHelper::ValidateSendToPSO($request);

        $activity = new IFSPSOActivityService($request->base_url, $request->token, $request->username, $request->password, $request->account_id, $request->send_to_pso);

        if ($request->send_to_pso && !$activity->isAuthenticated()) {
            return response()->json([
                'status' => 401,
                'description' => 'did not pass auth'
            ]);

        }
        return $activity->createActivity($request);
    }


    /**
     * Update the specified resource in storage.
     * // use this for swb broadcast response
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
    public function destroy(Request $request, $activity_id)
    {
        //

        $request->validate([
            'send_to_pso' => 'boolean',
            'base_url' => ['url', 'required_if:send_to_pso,true', 'not_regex:/prod|prd/i'],
            'dataset_id' => 'string|required',
            'account_id' => 'string|required_if:send_to_pso,true',
            'token' => 'string',
            'username' => 'string',
            'password' => 'string'
        ]);

        $request->merge(compact('activity_id'));

        PSOHelper::ValidateSendToPSO($request);


        $activity = new IFSPSOActivityService($request->base_url, $request->token, $request->username, $request->password, $request->account_id, $request->send_to_pso);

        if ($request->send_to_pso && !$activity->isAuthenticated()) {
            return response()->json([
                'status' => 401,
                'description' => 'did not pass auth'
            ]);

        }
        return $activity->deleteActivity($request);

    }

    /**
     * @throws ValidationException
     */
    public function destroyMulti(Request $request)
    {
        //

        $request->validate([
            'send_to_pso' => 'boolean',
            'base_url' => ['url', 'required_if:send_to_pso,true', 'not_regex:/prod|prd/i'],
            'dataset_id' => 'string|required',
            'account_id' => 'string|required_if:send_to_pso,true',
            'token' => 'string',
            'activities' => 'array|required',
            'username' => 'string',
            'password' => 'string'
        ]);


        PSOHelper::ValidateSendToPSO($request);

        $activity = new IFSPSOActivityService($request->base_url, $request->token, $request->username, $request->password, $request->account_id, $request->send_to_pso);

        if ($request->send_to_pso && !$activity->isAuthenticated()) {
            return response()->json([
                'status' => 401,
                'description' => 'did not pass auth'
            ]);

        }
        return $activity->deleteActivities($request);

    }
}

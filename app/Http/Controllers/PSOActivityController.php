<?php

namespace App\Http\Controllers;

use App\Services\IFSPSOActivityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PSOActivityController extends Controller
{


    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'activity_id' => 'string',
            'activity_type_id' => 'string|required',
            'time_zone' => 'numeric|between:-24,24', // now made optional
            'relative_day' => 'integer|gt:-1',
            'duration' => 'integer|gt:0|required',
            'window_size' => 'integer|in:0,3,4|nullable',
            'send_to_pso' => 'boolean',
            'lat' => 'numeric|between:-90,90|required',
            'long' => 'numeric|between:-180,180|required',
            'base_url' => ['url', 'required_if:send_to_pso,true', 'not_regex:/prod|prd/i'],
            'dataset_id' => 'string|required',
            'account_id' => 'string|required_if:send_to_pso,true',
            'token' => 'string',
            'username' => 'string',
            'password' => 'string'
        ]);

        Validator::make($request->all(), [
            'token' => Rule::requiredIf($request->send_to_pso == true && !$request->username && !$request->password)
        ])->validate();

        Validator::make($request->all(), [
            'username' => Rule::requiredIf($request->send_to_pso == true && !$request->token)
        ])->validate();

        Validator::make($request->all(), [
            'password' => Rule::requiredIf($request->send_to_pso == true && !$request->token)
        ])->validate();

        $activity = new IFSPSOActivityService($request->base_url, $request->token, $request->username, $request->password, $request->account_id, $request->send_to_pso);

        if (!$activity->isAuthenticated() && $request->send_to_pso) {
            return response()->json([
                'status' => 401,
                'description' => 'did not pass auth'
            ]);

        }
        return $activity->createActivity($request);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return Response
     */
    public function destroy(Request $request, $activity_id)
    {
        //
    }
}

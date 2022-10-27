<?php

namespace App\Http\Controllers;

use App\Services\IFSPSOActivityService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PSOActivitySLAController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        //

    }


    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
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
     * @param Request $request
     * @param $activity_id
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function destroy(Request $request, $activity_id)
    {
        //
        $request->merge(['activity_id' => $activity_id]);

        $request->validate([
            'send_to_pso' => 'boolean',
            'base_url' => ['url', 'required_if:send_to_pso,true', 'not_regex:/prod|prd/i'],
            'dataset_id' => 'string|required',
            'account_id' => 'string|required_if:send_to_pso,true',
            'token' => 'string',
            'username' => 'string',
            'password' => 'string',
            'activity_id' => 'string|required',
            'sla_type_id' => 'string|required',
            'prioirty' => 'numeric',
            'start_based' => 'boolean'
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

        return $activity->deleteSLA($request);

    }
}

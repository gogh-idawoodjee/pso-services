<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\PSOHelper;
use App\Http\Controllers\Controller;
use App\Services\V1\IFSPSOActivityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PSOActivityStatusController extends Controller
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
     * @param $activity_id
     * @param $status
     * @return JsonResponse
     * @throws ValidationException
     */
    public function update(Request $request, $activity_id, $status)
    {
        $statuses = collect(config('pso-services.statuses.all'))->keys()->toArray();

        Validator::make(compact('status'), [
            'status' => Rule::in($statuses)
        ])->validate();

        // todo make this in the new proper format
//        Validator::make($request->all(), [
//
//            'role_id' => Rule::requiredIf($request->user()->is_admin),
//
//        ]);

        $request->validate([
            'send_to_pso' => 'boolean',
            'base_url' => ['url', 'required_if:send_to_pso,true', 'not_regex:/prod|prd/i'],
            'dataset_id' => 'string|required',
            'account_id' => 'string|required_if:send_to_pso,true',
            'token' => 'string',
            'username' => 'string',
            'password' => 'string',
            'resource_id' => 'string|required_if:status,travelling,committed,sent,downloaded,accepted,waiting,onsite,pendingcompletion,visitcomplete,completed,incomplete',
            'date_time_fixed' => 'date_format:Y-m-d\TH:i',
        ]);

        $request->merge(compact('activity_id'));
        $request->merge(compact('status'));

        PSOHelper::ValidateSendToPSO($request);


        $activity = new IFSPSOActivityService($request->base_url, $request->token, $request->username, $request->password, $request->account_id, $request->send_to_pso);

        if ($request->send_to_pso && !$activity->isAuthenticated()) {
            return PSOHelper::notAuth();

        }

        return $activity->updateActivityStatus($request, $status);
    }

}

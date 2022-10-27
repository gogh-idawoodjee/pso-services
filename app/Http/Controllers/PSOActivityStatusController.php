<?php

namespace App\Http\Controllers;

use App\Services\IFSPSOActivityService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PSOActivityStatusController extends Controller
{

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
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
     * @return Response
     * @throws ValidationException
     */
    public function update(Request $request, $activity_id, $status)
    {
        //


        $request->merge(['activity_id' => $activity_id]);
        $request->merge(['status' => $status]);

        $request->validate([
            'send_to_pso' => 'boolean',
            'base_url' => ['url', 'required_if:send_to_pso,true', 'not_regex:/prod|prd/i'],
            'dataset_id' => 'string|required',
            'account_id' => 'string|required_if:send_to_pso,true',
            'token' => 'string',
            'username' => 'string',
            'password' => 'string',
            'activity_id' => 'string|required',
            'resource_id' => 'string|required_if:status,travelling,committed,sent,downloaded,accepted,waiting,onsite,pendingcompletion,visitcomplete,completed,incomplete',
            'date_time_fixed' => 'date|required_if:status,travelling,committed,sent,downloaded,accepted,waiting,onsite,pendingcompletion,visitcomplete,completed,incomplete',
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

//        Validator::make(['status' => $status], [
//            'status' => Rule::in([
//                'travelling', 'ignore', 'committed', 'sent', 'unallocated', 'downloaded', 'accepted', 'waiting', 'onsite',
//                'pendingcompletion', 'visitcomplete', 'completed', 'incomplete'
//            ])
//        ])->validate();
//
//        Validator::make([$request->all()], [
//            'resource_id' => Rule::requiredIf(in_array($status, $statuses_requiring_resources))
//        ])->validate();

        $activity = new IFSPSOActivityService($request->base_url, $request->token, $request->username, $request->password, $request->account_id, $request->send_to_pso);

        return $activity->updateActivityStatus($request, $status);

    }

}

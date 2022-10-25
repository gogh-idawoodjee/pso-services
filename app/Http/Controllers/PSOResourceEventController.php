<?php

namespace App\Http\Controllers;

use App\Services\IFSPSOResourceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;


class PSOResourceEventController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        //
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @param $resource_id
     * @return JsonResponse
     */
    public function store(Request $request, $resource_id)
    {
        //
        Log::channel('papertrail')->info($request);

        $request->validate([
            'event_type' => 'required|in:AO,AF,BO,BF,CE,FIX,RO,RF',
            'lat' => 'numeric|between:-90,90|required_with:long|required_if:event_type,FIX',
            'long' => 'numeric|between:-180,180|required_with:lat|required_if:event_type,FIX',
            'dataset_id' => 'required|string',
            'send_to_pso' => 'boolean',
            'base_url' => ['url', 'required_if:send_to_pso,true', 'not_regex:/prod|prd/i'],
            'account_id' => 'string|required_if:send_to_pso,true',
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

        // dig out the resource; will need the token at this point in real life
//        $resource_init = new IFSPSOGarabageService($request->account_id, $request->base_url, $request->token, $request->username, $request->password, $request->send_to_pso, 'cb847e5e-8747-4a02-9322-76530ef38a19');
        $resource_init = new IFSPSOResourceService($request->base_url, $request->token, $request->username, $request->password, $request->account_id, $request->send_to_pso);

        return $resource_init->setEvent($request, $resource_id);

    }

}

<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Services\IFSPSOResourceService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

use Illuminate\Validation\ValidationException;


class PSOResourceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     * @throws ValidationException
     */
    public function index(Request $request)//: Response
    {

        $request->validate([
            'dataset_id' => 'required|string',
            'token' => 'string',
            'username' => 'string',
            'account_id' => 'string|required',
            'password' => 'string',
            'base_url' => ['url', 'required', 'not_regex:/prod|prd|pd/i'],

        ]);

        Helper::ValidateSendToPSO($request);

        // need token if no user/pass, should default $requires_auth to true
        $resource_init = new IFSPSOResourceService($request->base_url, $request->token, $request->username, $request->password, $request->account_id, $request->send_to_pso);

        return response(['resources' => collect($resource_init->getScheduleableResources($request)),], 200)
            ->header('Content-Type', 'application/json');

    }


    /**
     * Display the specified resource.
     *
     * @param Request $request
     * @param $resource_id
     * @return Response
     */
    public function show(Request $request, $resource_id)
    {
        //
        $request->validate([
            'dataset_id' => 'required|string',
            'token' => 'string',
            'username' => 'string',
            'password' => 'string',
            'base_url' => ['string', 'required', 'not_regex:/prod|prd/i'],
        ]);

        $resource_init = new IFSPSOResourceService($request->base_url, $request->token, $request->username, $request->password, $request->account_id, true, 'cb847e5e-8747-4a02-9322-76530ef38a19');

        return $resource_init->getResourceForWebApp($resource_id, $request->dataset_id, $request->base_url);

    }


}

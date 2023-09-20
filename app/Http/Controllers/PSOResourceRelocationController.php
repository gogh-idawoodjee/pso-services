<?php

namespace App\Http\Controllers;

use App\Helpers\PSOHelper;
use App\Services\IFSPSOResourceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use JsonException;

class PSOResourceRelocationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return void
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        //
    }

    /**
     * @throws ValidationException
     */
    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @param $resource_id
     * @return JsonResponse
     * @throws ValidationException
     * @throws JsonException
     */
    public function store(Request $request, $resource_id)
    {


        $request->validate([

            'source_dataset_id' => 'string|required',
            'destination_dataset_id' => 'string|required',
            'start_datetime' => 'date', // will default to tomorrow  12:01am
            'end_datetime' => 'date|required',
            'resource_type_id' => 'string', // check
            'regions' => 'array',
            'region_parent' => 'string',
            'region_category' => 'string',
            'excluded_regions' => 'array',
            'retain_regions' => 'boolean',
            'skills' => 'array',
            'excluded_skills' => 'array',
            'lat' => 'numeric|between:-90,90', // check
            'long' => 'numeric|between:-180,180', // check
            'base_url' => ['url', 'required_if:send_to_pso,true', 'not_regex:/prod|prd/i'],
            'account_id' => 'string|required_if:send_to_pso,true',
            'token' => 'string',
            'username' => 'string',
            'password' => 'string'
        ]);

        PSOHelper::ValidateSendToPSO($request);

        $resource = new IFSPSOResourceService($request->base_url, $request->token, $request->username, $request->password, $request->account_id, $request->send_to_pso);

        if ($request->send_to_pso && !$resource->isAuthenticated()) {
            return response()->json([
                'status' => 401,
                'description' => 'did not pass auth'
            ]);

        }
        return $resource->relocateResource($request, $resource_id);
    }


    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        //
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
    public
    function destroy($id)
    {
        //
    }

}

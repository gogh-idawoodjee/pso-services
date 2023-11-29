<?php

namespace App\Http\Controllers;

use App\Helpers\PSOHelper;
use App\Services\IFSPSOResourceService;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use JsonException;


class PSOResourceController extends Controller
{

    /**
     * @throws ValidationException|JsonException
     */
    public function store(Request $request)
    {
        $request->validate([

            'first_name' => 'string',
            'surname' => 'string',
            'resource_type_id' => 'string|required',
            'resources_to_create' => 'integer|min:1|max:50',
            'send_to_pso' => 'boolean',
            'lat' => 'array',
            'long' => 'array',
            'names' => 'array',
            'ids' => 'array',
            'base_url' => ['url', 'required_if:send_to_pso,true', 'not_regex:/prod|prd/i'],
            'modelling_dataset_id' => 'string|required',
            'account_id' => 'string|required_if:send_to_pso,true',
            'token' => 'string',
            'username' => 'string',
            'password' => 'string'
        ]);

        PSOHelper::ValidateSendToPSO($request);

        $resource = new IFSPSOResourceService($request->base_url, $request->token, $request->username, $request->password, $request->account_id, $request->send_to_pso);

        if ($request->send_to_pso && !$resource->isAuthenticated()) {
            return PSOHelper::notAuth();

        }
        return $resource->createResource($request);

    }

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

        PSOHelper::ValidateSendToPSO($request);

        // need token if no user/pass, should default $requires_auth to true
        $resource_init = new IFSPSOResourceService($request->base_url, $request->token, $request->username, $request->password, $request->account_id, $request->send_to_pso);

        return response(['resources' => $resource_init->getScheduleableResources($request),], 200)
            ->header('Content-Type', 'application/json');

    }


    /**
     * Display the specified resource.
     *
     * @param Request $request
     * @param $resource_id
     * @return Application|ResponseFactory|JsonResponse|Response
     * @throws ValidationException
     * @throws Exception
     */
    public function show(Request $request, $resource_id)
    {
        //
        $request->validate([
            'dataset_id' => 'required|string',
            'token' => 'required|string',
            'account_id' => 'string|required',
            'base_url' => ['url', 'required', 'not_regex:/prod|prd/i'],
        ]);

        PSOHelper::ValidateCredentials($request);

        $resource_init = new IFSPSOResourceService($request->base_url, $request->token, $request->username, $request->password, $request->account_id, true);
        if (!$resource_init->isAuthenticated()) {
            return PSOHelper::notAuth();

        }

        return $resource_init->getResourceForWebApp($resource_id, $request->dataset_id, $request->base_url);

    }

}

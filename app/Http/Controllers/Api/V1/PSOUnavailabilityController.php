<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\PSOHelper;
use App\Http\Controllers\Controller;
use App\Services\IFSPSOResourceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PSOUnavailabilityController extends Controller
{

    /**
     * create an unavailability
     *
     * @param Request $request
     * @param $resource_id
     * @return JsonResponse
     * @throws ValidationException
     */
    public function store(Request $request, $resource_id): JsonResponse
    {

        $request->validate([
            'description' => 'string:2000',
            'category_id' => 'string|required',
            'duration' => 'numeric|gt:0|required',
            'time_zone' => 'numeric|between:-24,24', // now made optional
            'base_time' => 'date_format:Y-m-d\TH:i|required',
            'send_to_pso' => 'boolean',
            'base_url' => ['url', 'required_if:send_to_pso,true', 'not_regex:/prod|prd/i'],
            'rota_id' => 'string|required_if:send_to_pso,true',
            'dataset_id' => 'string|required',
            'account_id' => 'string|required_if:send_to_pso,true',
            'token' => 'string',
            'username' => 'string',
            'password' => 'string'
        ]);

        PSOHelper::ValidateSendToPSO($request);

        $resource_init = new IFSPSOResourceService($request->base_url, $request->token, $request->username, $request->password, $request->account_id, $request->send_to_pso);

        if ($request->send_to_pso && !$resource_init->isAuthenticated()) {
            return PSOHelper::notAuth();

        }
        return $resource_init->createUnavailability($request, $resource_id);

    }


    /**
     * updates one or more unavailabilities
     *
     * @param Request $request
     * @param $unavailability_id
     * @return JsonResponse
     * @throws ValidationException
     */
    public function update(Request $request, $unavailability_id)//: JsonResponse
    {

//        $request->merge(['unavailability_id' => $unavailability_id]);
        $request->validate([
            'description' => 'string:2000',
            'category_id' => 'string',
            'send_to_pso' => 'boolean',
            'duration' => 'numeric|between:0,24',
            'time_zone' => 'numeric|between:-24,24', // now made optional
            'base_time' => 'date_format:Y-m-d\TH:i',
            'base_url' => ['url', 'required', 'not_regex:/prod|prd/i'],
            'rota_id' => 'string|required',
            'dataset_id' => 'string|required',
            'account_id' => 'string|required',
            'token' => 'string',
            'username' => 'string',
            'password' => 'string'
        ]);

        PSOHelper::ValidateSendToPSO($request);

        $resource_init = new IFSPSOResourceService($request->base_url, $request->token, $request->username, $request->password, $request->account_id, true);

        if ($request->send_to_pso && !$resource_init->isAuthenticated()) {
            return PSOHelper::notAuth();

        }
        return $resource_init->updateUnavailability($request, $unavailability_id);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @param $unavailability_id
     * @return JsonResponse
     * @throws ValidationException
     */
    public function destroy(Request $request, $unavailability_id): JsonResponse
    {

        $request->merge(compact('unavailability_id'));
        $request->validate([
            'send_to_pso' => 'boolean',
            'base_url' => ['url', 'required_if:send_to_pso,true', 'not_regex:/prod|prd/i'],
            'rota_id' => 'string|required_if:send_to_pso,true',
            'dataset_id' => 'string|required',
            'unavailability_id' => 'string|required',
            'account_id' => 'string|required_if:send_to_pso,true',
            'token' => 'string',
            'username' => 'string',
            'password' => 'string'
        ]);

        PSOHelper::ValidateSendToPSO($request);

        $resource_init = new IFSPSOResourceService($request->base_url, $request->token, $request->username, $request->password, $request->account_id, $request->send_to_pso);

        if ($request->send_to_pso && !$resource_init->isAuthenticated()) {
            return PSOHelper::notAuth();
        }

        return $resource_init->DeleteUnavailability($request);

    }
}

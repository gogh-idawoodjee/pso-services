<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Services\IFSPSOAssistService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use Illuminate\Validation\ValidationException;

class PSOAssistController extends Controller
{

    /**
     * send an init to PSO.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function store(Request $request): JsonResponse
    {

        $request->validate([
            'send_to_pso' => 'boolean',
            'base_url' => ['url', 'required_if:send_to_pso,true', 'not_regex:/prod|prd/i'],
            'dataset_id' => 'string|required',
            'rota_id' => 'string', // if not included, assume same as dataset ID
            'account_id' => 'string|required_if:send_to_pso,true',
            'token' => 'string',
            'username' => 'string',
            'password' => 'string',
            'dse_duration' => 'numeric|required',
            'appointment_window' => 'numeric',
            'process_type' => 'string|in:DYNAMIC,APPOINTMENT,REACTIVE,STATIC',
            'description' => 'string',
            'datetime' => 'date',
            'include_broadcast' => 'boolean',
            'broadcast_type' => 'integer|required_if:include_broadcast,true',
            'broadcast_url' => 'url|required_if:include_broadcast,true'
        ]);

        Helper::ValidateSendToPSO($request);

        $init = new IFSPSOAssistService($request->base_url, $request->token, $request->username, $request->password, $request->account_id, $request->send_to_pso);

        if (!$init->isAuthenticated() && $request->send_to_pso) {
            return response()->json([
                'status' => 401,
                'description' => 'did not pass auth'
            ]);
        }

        return $init->InitializePSO($request);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function update(Request $request): JsonResponse
    {

        $request->validate([
            'send_to_pso' => 'boolean',
            'base_url' => ['url', 'required_if:send_to_pso,true', 'not_regex:/prod|prd/i'],
            'dataset_id' => 'string|required',
            'rota_id' => 'string', // if not included, assume same as dataset ID
            'account_id' => 'string|required_if:send_to_pso,true',
            'token' => 'string',
            'username' => 'string',
            'password' => 'string',
            'description' => 'string',
            'datetime' => 'date',
            'include_broadcast' => 'boolean',
            'broadcast_type' => 'integer|required_if:include_broadcast,true',
            'broadcast_url' => 'url|required_if:include_broadcast,true'
        ]);

        Helper::ValidateSendToPSO($request);

        $rotatodse = new IFSPSOAssistService($request->base_url, $request->token, $request->username, $request->password, $request->account_id, $request->send_to_pso);

        if (!$rotatodse->isAuthenticated() && $request->send_to_pso) {
            return response()->json([
                'status' => 401,
                'description' => 'did not pass auth'
            ]);

        }

        return $rotatodse->sendRotaToDSE(
            $request->dataset_id,
            $request->rota_id,
            $request->base_url,
            $request->datetime,
            $request->send_to_pso,
            $request->include_broadcast,
            $request->broadcast_type,
            $request->broadcast_url,

        );
    }

    public function index(Request $request)
    {
        $request->validate([
            'base_url' => ['url', 'required', 'not_regex:/prod|prd/i'],
            'dataset_id' => 'string|required',
            'account_id' => 'string|required',
            'token' => 'string|required',
            'mindate' => 'date_format:Y-m-d',
            'maxdate' => 'date_format:Y-m-d'
        ]);

        $usage_data = new IFSPSOAssistService($request->base_url, $request->token, $request->username, $request->password, $request->account_id, true);


        if ($usage_data->isAuthenticated()) {
            return $usage_data->getUsageData($request);
        }

        return response()->json([
            'status' => 401,
            'description' => 'did not pass auth'
        ]);
    }

}

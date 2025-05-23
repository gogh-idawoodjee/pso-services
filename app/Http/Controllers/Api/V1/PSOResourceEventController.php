<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\PSOHelper;
use App\Http\Controllers\Controller;
use App\Services\V1\IFSPSOResourceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;


class PSOResourceEventController extends Controller
{
    /**
     * Create a new resource event
     *
     * @param Request $request
     * @param $resource_id
     * @return JsonResponse
     * @throws ValidationException
     */
    public function store(Request $request, $resource_id)
    {

        $request->validate([
            'event_type' => 'required|in:AO,AF,BO,BF,CE,FIX,RO,RF',
            'lat' => 'numeric|between:-90,90|required_with:long|required_if:event_type,FIX',
            'long' => 'numeric|between:-180,180|required_with:lat|required_if:event_type,FIX',
            'event_date_time' => 'date',
            'dataset_id' => 'required|string',
            'send_to_pso' => 'boolean',
            'base_url' => ['url', 'required_if:send_to_pso,true', 'not_regex:/prod|prd/i'],
            'account_id' => 'string|required_if:send_to_pso,true',
            'username' => 'string',
            'password' => 'string'
        ]);

        PSOHelper::ValidateSendToPSO($request);

        $resource_init = new IFSPSOResourceService($request->base_url, $request->token, $request->username, $request->password, $request->account_id, $request->send_to_pso);

        if ($request->send_to_pso && !$resource_init->isAuthenticated()) {
            return response()->json([
                'status' => 401,
                'description' => 'did not pass auth'
            ]);
        }

        return $resource_init->setEvent($request, $resource_id);

    }

}

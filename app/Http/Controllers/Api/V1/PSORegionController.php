<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\PSOHelper;
use App\Http\Controllers\Controller;
use App\Services\IFSPSOModellingDataService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PSORegionController extends Controller
{


    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function store(Request $request)
    {
        //
        $request->validate([
            'send_to_pso' => 'boolean',
            'base_url' => ['url', 'required_if:send_to_pso,true', 'not_regex:/prod|prd/i'],
            'dataset_id' => 'string|required',
            'account_id' => 'string|required_if:send_to_pso,true',
            'description' => 'array',
            'region_parent' => 'string',
            'region_category' => 'string',
            'send' => 'boolean',
            'token' => 'string',
            'region' => 'array|required',
            'username' => 'string',
            'password' => 'string'
        ]);


        PSOHelper::ValidateSendToPSO($request);

        $region = new IFSPSOModellingDataService($request->base_url, $request->token, $request->username, $request->password, $request->account_id, $request->send_to_pso);

        if ($request->send_to_pso && !$region->isAuthenticated()) {
            return PSOHelper::notAuth();
        }

        return $region->createDivision($request);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return void
     */
    public function destroy($id)
    {
        //
    }
}

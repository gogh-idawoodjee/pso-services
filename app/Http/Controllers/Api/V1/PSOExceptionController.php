<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\PSOHelper;
use App\Http\Controllers\Controller;
use App\Services\V1\IFSPSOExceptionService;
use Illuminate\Http\Request;

class PSOExceptionController extends Controller
{


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'send_to_pso' => 'boolean',
            'base_url' => ['url', 'required_if:send_to_pso,true', 'not_regex:/prod|prd/i'],
            'dataset_id' => 'string|required',
            'account_id' => 'string|required_if:send_to_pso,true',
            'token' => 'string',
            'username' => 'string',
            'password' => 'string',
            'schedule_exception_type_id' => 'numeric|required',
            'activity_id' => 'string|required_without:resource_id',
            'resource_id' => 'string|required_without:activity_id',
            'label' => 'string|max:32|required',
            'value' => 'string|max:64|required'
        ]);

        PSOHelper::ValidateSendToPSO($request);

        $exception = new IFSPSOExceptionService($request->base_url, $request->token, $request->username, $request->password, $request->account_id, $request->send_to_pso);

        if ($request->send_to_pso && !$exception->isAuthenticated()) {
            return PSOHelper::notAuth();
        }

        return $exception->ExceptionPayload($request);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}

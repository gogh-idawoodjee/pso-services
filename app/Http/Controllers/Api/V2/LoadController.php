<?php

namespace App\Http\Controllers\Api\V2;

use App\DataTransferObjects\PsoContext;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V2\LoadPsoRequest;
use App\Http\Requests\Api\V2\UpdateRotaRequest;
use App\Services\V2\LoadService;
use App\Traits\V2\PSOAssistV2;
use Illuminate\Http\JsonResponse;

/**
 * @group System
 */
class LoadController extends Controller
{
    use PSOAssistV2;

    /**
     * Initialize PSO
     *
     * @response 200 scenario="Sent to PSO" {"data": {"payloadToPso": {"dsScheduleData": {"@xmlns": "http://360Scheduling.com/Schema/dsScheduleData.xsd", "Input_Reference": {"id": "abc123", "input_type": "LOAD", "dataset_id": "dataset_123", "organisation_id": "2"}}}, "responseFromPso": {}}, "status": 200, "message": "Successful. Sent to PSO"}
     * @response 202 scenario="Dry run" {"data": {"payloadToPso": {"dsScheduleData": {"@xmlns": "http://360Scheduling.com/Schema/dsScheduleData.xsd", "Input_Reference": {"id": "abc123", "input_type": "LOAD", "dataset_id": "dataset_123", "organisation_id": "2"}}}}, "status": 202, "message": "Successful. Not sent to PSO by Request"}
     */
    public function store(LoadPsoRequest $request, LoadService $loadService): JsonResponse
    {
        return $this->executeAuthenticatedAction($request, fn(LoadPsoRequest $req) =>
            $loadService->loadPSO(PsoContext::fromRequest($req))
        );
    }

    /**
     * Send Rota to DSE
     *
     * @response 200 scenario="Sent to PSO" {"data": {}, "status": 200, "message": "Successful. Sent to PSO"}
     * @response 202 scenario="Dry run" {"data": {}, "status": 202, "message": "Successful. Not sent to PSO by Request"}
     */
    public function updateRota(UpdateRotaRequest $request, LoadService $loadService): JsonResponse
    {
        return $this->executeAuthenticatedAction($request, fn(UpdateRotaRequest $req) =>
            $loadService->updateRota(PsoContext::fromRequest($req))
        );
    }
}

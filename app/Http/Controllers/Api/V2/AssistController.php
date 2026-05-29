<?php

namespace App\Http\Controllers\Api\V2;

use App\DataTransferObjects\PsoContext;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V2\DeleteObjectRequest;
use App\Http\Requests\Api\V2\LoadPsoRequest;
use App\Http\Requests\Api\V2\SystemUsageRequest;
use App\Http\Requests\Api\V2\UpdateRotaRequest;
use App\Services\V2\AssistService;
use App\Services\V2\DeleteService;
use App\Services\V2\LoadService;
use App\Traits\V2\PSOAssistV2;
use Illuminate\Http\JsonResponse;

/**
 * @group System
 */
class AssistController extends Controller
{
    use PSOAssistV2;

    /**
     * Get System Usage
     *
     * @response 200 scenario="Success" {"data": {}, "status": 200}
     */
    public function show(SystemUsageRequest $request, AssistService $assistService): JsonResponse
    {
        return $this->executeAuthenticatedAction($request, fn(SystemUsageRequest $req) =>
            $assistService->getSystemUsage(PsoContext::fromRequest($req))
        );
    }

    /**
     * Generic Delete Service
     *
     * @response 200 scenario="Sent to PSO" {"data": {"payloadToPso": {"dsScheduleData": {"@xmlns": "http://360Scheduling.com/Schema/dsScheduleData.xsd", "Object_Deletion": [{"object_type_id": "Activity", "object_pk1": "ACT-001", "object_pk_name1": "id"}]}}, "responseFromPso": {}}, "status": 200, "message": "Successful. Sent to PSO"}
     * @response 202 scenario="Dry run" {"data": {"payloadToPso": {"dsScheduleData": {"@xmlns": "http://360Scheduling.com/Schema/dsScheduleData.xsd", "Object_Deletion": [{"object_type_id": "Activity", "object_pk1": "ACT-001", "object_pk_name1": "id"}]}}}, "status": 202, "message": "Successful. Not sent to PSO by Request"}
     */
    public function destroy(DeleteObjectRequest $request, DeleteService $deleteService): JsonResponse
    {
        return $this->executeAuthenticatedAction($request, fn(DeleteObjectRequest $req) =>
            $deleteService->deleteObject(PsoContext::fromRequest($req))
        );
    }

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
    public function update(UpdateRotaRequest $request, LoadService $loadService): JsonResponse
    {
        return $this->executeAuthenticatedAction($request, fn(UpdateRotaRequest $req) =>
            $loadService->updateRota(PsoContext::fromRequest($req))
        );
    }
}

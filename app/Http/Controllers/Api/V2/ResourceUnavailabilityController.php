<?php

namespace App\Http\Controllers\Api\V2;

use App\DataTransferObjects\PsoContext;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V2\UnavailabilityRequest;
use App\Http\Requests\Api\V2\UnavailabilityUpdateRequest;
use App\Services\V2\ResourceService;
use App\Traits\V2\PSOAssistV2;
use Illuminate\Http\JsonResponse;

/**
 * @group Resources
 */
class ResourceUnavailabilityController extends Controller
{
    use PSOAssistV2;

    /**
     * Create a new resource unavailability.
     *
     * @response 200 scenario="Sent to PSO" {"data": {"payloadToPso": {"dsScheduleData": {"@xmlns": "http://360Scheduling.com/Schema/dsScheduleData.xsd"}}, "responseFromPso": {}}, "status": 200, "message": "Successful. Sent to PSO"}
     * @response 202 scenario="Dry run" {"data": {"payloadToPso": {"dsScheduleData": {"@xmlns": "http://360Scheduling.com/Schema/dsScheduleData.xsd"}}}, "status": 202, "message": "Successful. Not sent to PSO by Request"}
     */
    public function store(UnavailabilityRequest $request, ResourceService $resourceService): JsonResponse
    {
        return $this->executeAuthenticatedAction($request, fn(UnavailabilityRequest $req) =>
            $resourceService->createUnavailability(PsoContext::fromRequest($req))
        );
    }

    /**
     * Update one or more existing unavailabilities.
     *
     * @response 200 scenario="Sent to PSO" {"data": {"payloadToPso": {"dsScheduleData": {"@xmlns": "http://360Scheduling.com/Schema/dsScheduleData.xsd"}}, "responseFromPso": {}}, "status": 200, "message": "Successful. Sent to PSO"}
     * @response 202 scenario="Dry run" {"data": {"payloadToPso": {"dsScheduleData": {"@xmlns": "http://360Scheduling.com/Schema/dsScheduleData.xsd"}}}, "status": 202, "message": "Successful. Not sent to PSO by Request"}
     */
    public function update(UnavailabilityUpdateRequest $request, ResourceService $resourceService): JsonResponse
    {
        return $this->executeAuthenticatedAction($request, fn(UnavailabilityUpdateRequest $req) =>
            $resourceService->updateUnavailability(PsoContext::fromRequest($req))
        );
    }
}

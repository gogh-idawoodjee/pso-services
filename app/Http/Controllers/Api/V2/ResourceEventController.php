<?php

namespace App\Http\Controllers\Api\V2;

use App\DataTransferObjects\PsoContext;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V2\ResourceEventRequest;
use App\Services\V2\ResourceService;
use App\Traits\V2\PSOAssistV2;
use Illuminate\Http\JsonResponse;

/**
 * @group Resources
 */
class ResourceEventController extends Controller
{
    use PSOAssistV2;

    /**
     * Create a new resource event.
     *
     * @response 200 scenario="Sent to PSO" {"data": {"payloadToPso": {"dsScheduleData": {"@xmlns": "http://360Scheduling.com/Schema/dsScheduleData.xsd", "Schedule_Event": [{"id": "evt-abc123", "event_type_id": "START", "resource_id": "RES-001", "event_date_time": "2025-05-29T08:00:00+00:00"}]}}, "responseFromPso": {}}, "status": 200, "message": "Successful. Sent to PSO"}
     * @response 202 scenario="Dry run" {"data": {"payloadToPso": {"dsScheduleData": {"@xmlns": "http://360Scheduling.com/Schema/dsScheduleData.xsd", "Schedule_Event": [{"id": "evt-abc123", "event_type_id": "START", "resource_id": "RES-001", "event_date_time": "2025-05-29T08:00:00+00:00"}]}}}, "status": 202, "message": "Successful. Not sent to PSO by Request"}
     */
    public function store(ResourceEventRequest $request, ResourceService $resourceService): JsonResponse
    {
        return $this->executeAuthenticatedAction($request, fn(ResourceEventRequest $req) =>
            $resourceService->createEvent(PsoContext::fromRequest($req))
        );
    }
}

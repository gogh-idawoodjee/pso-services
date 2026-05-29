<?php

namespace App\Http\Controllers\Api\V2;

use App\DataTransferObjects\PsoContext;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V2\ActivityStatusRequest;
use App\Services\V2\ActivityService;
use App\Traits\V2\PSOAssistV2;
use Illuminate\Http\JsonResponse;

/**
 * @group Activities
 */
class ActivityStatusController extends Controller
{
    use PSOAssistV2;

    /**
     * Update the Task Status of an Activity.
     *
     * @response 200 scenario="Sent to PSO" {"data": {"payloadToPso": {"dsScheduleData": {"@xmlns": "http://360Scheduling.com/Schema/dsScheduleData.xsd", "Activity_Status": {"activity_id": "ACT-001", "status_id": "allocated", "resource_id": "RES-001"}}}, "responseFromPso": {}}, "status": 200, "message": "Successful. Sent to PSO"}
     * @response 202 scenario="Dry run" {"data": {"payloadToPso": {"dsScheduleData": {"@xmlns": "http://360Scheduling.com/Schema/dsScheduleData.xsd", "Activity_Status": {"activity_id": "ACT-001", "status_id": "allocated"}}}}, "status": 202, "message": "Successful. Not sent to PSO by Request"}
     */
    public function update(ActivityStatusRequest $request, ActivityService $activityService): JsonResponse
    {
        return $this->executeAuthenticatedAction($request, fn(ActivityStatusRequest $req) =>
            $activityService->updateStatus(
                PsoContext::fromRequest($req),
                $req->activityStatus(),
                $req->input('data.resourceId'),
            )
        );
    }
}

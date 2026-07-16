<?php

namespace App\Http\Controllers\Api\V2;

use App\DataTransferObjects\PsoContext;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V2\ActivityDeleteRequest;
use App\Services\V2\ActivityService;
use App\Traits\V2\PSOAssistV2;
use Illuminate\Http\JsonResponse;

/**
 * @group Activities
 */
class ActivityController extends Controller
{
    use PSOAssistV2;

    /**
     * Delete Activity or Activities.
     *
     * Deletes one or more activities.
     *
     * @response 200 scenario="Sent to PSO" {"data": {"payloadToPso": {"dsScheduleData": {"@xmlns": "http://360Scheduling.com/Schema/dsScheduleData.xsd", "Object_Deletion": [{"object_type_id": "Activity", "object_pk1": "ACT-001", "object_pk_name1": "id"}]}}, "responseFromPso": {}}, "status": 200, "message": "Successful. Sent to PSO"}
     * @response 202 scenario="Dry run" {"data": {"payloadToPso": {"dsScheduleData": {"@xmlns": "http://360Scheduling.com/Schema/dsScheduleData.xsd", "Object_Deletion": [{"object_type_id": "Activity", "object_pk1": "ACT-001", "object_pk_name1": "id"}]}}}, "status": 202, "message": "Successful. Not sent to PSO by Request"}
     */
    public function destroy(ActivityDeleteRequest $request, ActivityService $activityService): JsonResponse
    {
        return $this->executeAuthenticatedAction($request, fn(ActivityDeleteRequest $req) =>
            $activityService->deleteActivities(PsoContext::fromRequest($req))
        );
    }
}

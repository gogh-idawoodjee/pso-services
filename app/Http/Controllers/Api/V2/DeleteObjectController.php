<?php

namespace App\Http\Controllers\Api\V2;

use App\DataTransferObjects\PsoContext;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V2\DeleteObjectRequest;
use App\Services\V2\DeleteService;
use App\Traits\V2\PSOAssistV2;
use Illuminate\Http\JsonResponse;

/**
 * @group System
 */
class DeleteObjectController extends Controller
{
    use PSOAssistV2;

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
}

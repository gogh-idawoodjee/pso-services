<?php

namespace App\Http\Controllers\Api\V2;

use App\DataTransferObjects\PsoContext;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V2\ResourceShiftRequest;
use App\Services\V2\ResourceService;
use App\Traits\V2\PSOAssistV2;
use Illuminate\Http\JsonResponse;

/**
 * @group Resources
 */
class ResourceShiftController extends Controller
{
    use PSOAssistV2;

    /**
     * Update the specified resource shift.
     *
     * If this is a change to a shift in the ARP, after a successful transaction,
     * the API will update the resource's shift and send the updated rota to the DSE
     * (Rota Update) so the change is immediately recognized by the optimization process.
     *
     * @response 200 scenario="Sent to PSO" {"data": {"payloadToPso": {"dsScheduleData": {"@xmlns": "http://360Scheduling.com/Schema/dsScheduleData.xsd", "Shift": {"id": "SHIFT-001", "resource_id": "RES-001", "start_datetime": "2025-05-29T08:00:00", "end_datetime": "2025-05-29T17:00:00"}}}, "responseFromPso": {}}, "status": 200, "message": "Successful. Sent to PSO"}
     * @response 202 scenario="Dry run" {"data": {"payloadToPso": {"dsScheduleData": {"@xmlns": "http://360Scheduling.com/Schema/dsScheduleData.xsd", "Shift": {"id": "SHIFT-001", "resource_id": "RES-001", "start_datetime": "2025-05-29T08:00:00", "end_datetime": "2025-05-29T17:00:00"}}}}, "status": 202, "message": "Successful. Not sent to PSO by Request"}
     */
    public function update(ResourceShiftRequest $request, ResourceService $resourceService): JsonResponse
    {
        return $this->executeAuthenticatedAction($request, fn(ResourceShiftRequest $req) =>
            $resourceService->updateShift(PsoContext::fromRequest($req))
        );
    }
}

<?php

namespace App\Http\Controllers\Api\V2;

use App\DataTransferObjects\PsoContext;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V2\ScheduleExceptionRequest;
use App\Services\V2\ScheduleExceptionService;
use App\Traits\V2\PSOAssistV2;
use Illuminate\Http\JsonResponse;

/**
 * @group Schedule Exceptions
 */
class ScheduleExceptionController extends Controller
{
    use PSOAssistV2;

    /**
     * Create a new custom Exception.
     *
     * @response 200 scenario="Sent to PSO" {"data": {"payloadToPso": {"dsScheduleData": {"@xmlns": "http://360Scheduling.com/Schema/dsScheduleData.xsd", "Custom_Exception": {"id": "EXC-001", "schedule_exception_type_id": 1, "resource_id": "RES-001"}, "Custom_Exception_Data": {"custom_exception_id": "EXC-001", "label": "Sick Leave", "sequence": 1, "value": "2025-05-17"}}}, "responseFromPso": {}}, "status": 200, "message": "Successful. Sent to PSO"}
     * @response 202 scenario="Dry run" {"data": {"payloadToPso": {"dsScheduleData": {"@xmlns": "http://360Scheduling.com/Schema/dsScheduleData.xsd", "Custom_Exception": {"id": "EXC-001", "schedule_exception_type_id": 1, "resource_id": "RES-001"}, "Custom_Exception_Data": {"custom_exception_id": "EXC-001", "label": "Sick Leave", "sequence": 1, "value": "2025-05-17"}}}}, "status": 202, "message": "Successful. Not sent to PSO by Request"}
     */
    public function store(ScheduleExceptionRequest $request, ScheduleExceptionService $scheduleExceptionService): JsonResponse
    {
        return $this->executeAuthenticatedAction($request, fn(ScheduleExceptionRequest $req) =>
            $scheduleExceptionService->createException(PsoContext::fromRequest($req))
        );
    }
}

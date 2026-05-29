<?php

namespace App\Http\Controllers\Api\V2;

use App\DataTransferObjects\PsoContext;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V2\AppointmentSummaryRequest;
use App\Http\Requests\Api\V2\AppointmentRequest;
use App\Http\Resources\AppointmentResource;
use App\Models\V2\PSOAppointment;
use App\Services\V2\AppointmentService;
use App\Traits\V2\PSOAssistV2;
use Illuminate\Http\JsonResponse;

/**
 * @group Appointments
 */
class AppointmentController extends Controller
{
    use PSOAssistV2;

    /**
     * Check Appointed.
     *
     * @response 200 scenario="Sent to PSO" {"data": {}, "status": 200, "message": "Successful. Sent to PSO"}
     * @response 202 scenario="Dry run" {"data": {}, "status": 202, "message": "Successful. Not sent to PSO by Request"}
     */
    public function check(AppointmentSummaryRequest $request, AppointmentService $appointmentService): JsonResponse
    {
        return $this->executeAuthenticatedAction($request, fn(AppointmentSummaryRequest $req) =>
            $appointmentService->checkAppointed(PsoContext::fromRequest($req))
        );
    }

    /**
     * Get Appointments
     *
     * @bodyParam data.slaStart string required SLA start datetime. Example: 2025-06-01T08:00:00
     * @bodyParam data.slaEnd string required SLA end datetime. Example: 2025-06-01T17:00:00
     * @response 200 scenario="Sent to PSO" {"data": {"payloadToPso": {"dsScheduleData": {"@xmlns": "http://360Scheduling.com/Schema/dsScheduleData.xsd", "Appointment_Request": {"id": "abc123", "activity_id": "ACT-001_AB"}, "Activity": {"id": "ACT-001_AB"}, "Activity_Status": {"activity_id": "ACT-001_AB"}}}, "responseFromPso": {}}, "status": 200, "message": "Successful. Sent to PSO"}
     * @response 202 scenario="Dry run" {"data": {"payloadToPso": {"dsScheduleData": {"@xmlns": "http://360Scheduling.com/Schema/dsScheduleData.xsd", "Appointment_Request": {"id": "abc123", "activity_id": "ACT-001_AB"}, "Activity": {"id": "ACT-001_AB"}}}}, "status": 202, "message": "Successful. Not sent to PSO by Request"}
     */
    public function store(AppointmentRequest $request, AppointmentService $appointmentService): JsonResponse
    {
        return $this->executeAuthenticatedAction($request, fn(AppointmentRequest $req) =>
            $appointmentService->getAppointment(PsoContext::fromRequest($req))
        );
    }

    /**
     * Get Appointment Details
     *
     * @response 200 scenario="Found" {"data": {"id": "abc123", "status": "completed"}, "status": 200}
     */
    public function show(PSOAppointment $appointmentRequestId): JsonResponse
    {
        return $this->ok(new AppointmentResource($appointmentRequestId));
    }

    /**
     * Accept Appointment
     *
     * @response 200 scenario="Sent to PSO" {"data": {}, "status": 200, "message": "Successful. Sent to PSO"}
     * @response 202 scenario="Dry run" {"data": {}, "status": 202, "message": "Successful. Not sent to PSO by Request"}
     */
    public function update(AppointmentSummaryRequest $request, AppointmentService $appointmentService): JsonResponse
    {
        return $this->executeAuthenticatedAction($request, fn(AppointmentSummaryRequest $req) =>
            $appointmentService->acceptAppointment(PsoContext::fromRequest($req))
        );
    }

    /**
     * Decline Appointment
     *
     * @response 200 scenario="Sent to PSO" {"data": {}, "status": 200, "message": "Successful. Sent to PSO"}
     * @response 202 scenario="Dry run" {"data": {}, "status": 202, "message": "Successful. Not sent to PSO by Request"}
     */
    public function destroy(AppointmentSummaryRequest $request, AppointmentService $appointmentService): JsonResponse
    {
        return $this->executeAuthenticatedAction($request, fn(AppointmentSummaryRequest $req) =>
            $appointmentService->declineAppointment(PsoContext::fromRequest($req))
        );
    }
}

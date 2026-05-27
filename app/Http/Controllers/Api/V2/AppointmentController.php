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

class AppointmentController extends Controller
{
    use PSOAssistV2;

    /**
     * Check Appointed.
     */
    public function check(AppointmentSummaryRequest $request, AppointmentService $appointmentService): JsonResponse
    {
        return $this->executeAuthenticatedAction($request, fn(AppointmentSummaryRequest $req) =>
            $appointmentService->checkAppointed(PsoContext::fromRequest($req))
        );
    }

    /**
     * Get Appointments
     */
    public function store(AppointmentRequest $request, AppointmentService $appointmentService): JsonResponse
    {
        return $this->executeAuthenticatedAction($request, fn(AppointmentRequest $req) =>
            $appointmentService->getAppointment(PsoContext::fromRequest($req))
        );
    }

    /**
     * Get Appointment Details
     */
    public function show(PSOAppointment $appointmentRequestId): JsonResponse
    {
        return $this->ok(new AppointmentResource($appointmentRequestId));
    }

    /**
     * Accept Appointment
     */
    public function update(AppointmentSummaryRequest $request, AppointmentService $appointmentService): JsonResponse
    {
        return $this->executeAuthenticatedAction($request, fn(AppointmentSummaryRequest $req) =>
            $appointmentService->acceptAppointment(PsoContext::fromRequest($req))
        );
    }

    /**
     * Decline Appointment
     */
    public function destroy(AppointmentSummaryRequest $request, AppointmentService $appointmentService): JsonResponse
    {
        return $this->executeAuthenticatedAction($request, fn(AppointmentSummaryRequest $req) =>
            $appointmentService->declineAppointment(PsoContext::fromRequest($req))
        );
    }
}

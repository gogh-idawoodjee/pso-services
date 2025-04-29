<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V2\AppointmentSummaryRequest;
use App\Http\Requests\Api\V2\AppointmentRequest;

use App\Services\V2\AppointmentService;
use App\Traits\V2\PSOAssistV2;
use Illuminate\Http\JsonResponse;


class AppointmentController extends Controller
{

    use PSOAssistV2;

    /**
     * Check Appointed.
     */
    public function check(AppointmentSummaryRequest $request): JsonResponse
    {
        return $this->executeAuthenticatedAction($request, function (AppointmentSummaryRequest $req) {
            // so we have the token now in data_get($req, 'environment.token')

            // we should send that the activity service? // all our services should accept a token
            $appointmentService = new AppointmentService(

                $req->filled('environment.token') ? $req->input('environment.token') : null,
                $req->validated(),
            );

            return $appointmentService->checkAppointed();
        });
    }


    /**
     * Get Appointments
     */
    public function store(AppointmentRequest $request): JsonResponse
    {

        return $this->executeAuthenticatedAction($request, function (AppointmentRequest $req) {
            // so we have the token now in data_get($req, 'environment.token')

            // we should send that the activity service? // all our services should accept a token
            $appointmentService = new AppointmentService(

                $req->filled('environment.token') ? $req->input('environment.token') : null,
                $req->validated(),
            );

            return $appointmentService->getAppointment();
        });


    }

    /**
     * Get Appointment Details
     */
    public function show(string $AppointmentRequestId): JsonResponse
    {
        //
    }


    /**
     * Accept Appointment
     */
    public function update(AppointmentSummaryRequest $request): JsonResponse
    {

        return $this->executeAuthenticatedAction($request, function (AppointmentSummaryRequest $req) {
            // so we have the token now in data_get($req, 'environment.token')

            // we should send that the activity service? // all our services should accept a token
            $appointmentService = new AppointmentService(

                $req->filled('environment.token') ? $req->input('environment.token') : null,
                $req->validated(),
            );

            return $appointmentService->acceptAppointment();
        });

    }

    /**
     * Decline Appointment
     */
    public function destroy(AppointmentSummaryRequest $request): JsonResponse
    {
        return $this->executeAuthenticatedAction($request, function (AppointmentSummaryRequest $req) {
            // so we have the token now in data_get($req, 'environment.token')

            // we should send that the activity service? // all our services should accept a token
            $appointmentService = new AppointmentService(

                $req->filled('environment.token') ? $req->input('environment.token') : null,
                $req->validated(),
            );

            return $appointmentService->declineAppointment();
        });
    }
}

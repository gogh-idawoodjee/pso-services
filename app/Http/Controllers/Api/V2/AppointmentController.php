<?php

namespace App\Http\Controllers\Api\V2;

use App\DataTransferObjects\PsoContext;
use App\Http\Controllers\Controller;
use App\Http\OpenApi\NotSentToPso;
use App\Http\OpenApi\OkResponse;
use App\Http\OpenApi\SentToPso;
use App\Http\Requests\Api\V2\AppointmentRequest;
use App\Http\Requests\Api\V2\AppointmentSummaryRequest;
use App\Http\Resources\AppointmentResource;
use App\Models\V2\PSOAppointment;
use App\Services\V2\AppointmentService;
use App\Traits\V2\PSOAssistV2;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;

#[Group('Appointments')]
class AppointmentController extends Controller
{
    use PSOAssistV2;

    /**
     * Check Appointed.
     */
    #[SentToPso(data: 'array{appointment_summary: array{appointmentRequestId: string, isSlotAvailable: bool|null, responseFromPso: array<string, mixed>}, payloadToPso: object}', examples: [[
        'appointment_summary' => ['appointmentRequestId' => 'abc123', 'isSlotAvailable' => true, 'responseFromPso' => ['appointed' => true]],
        'payloadToPso' => ['dsScheduleData' => ['@xmlns' => 'http://360Scheduling.com/Schema/dsScheduleData.xsd', 'Appointment_Offer_Response' => ['appointment_request_id' => 'abc123', 'appointment_offer_id' => '1']]],
    ]])]
    #[NotSentToPso]
    public function check(AppointmentSummaryRequest $request, AppointmentService $appointmentService): JsonResponse
    {
        return $this->executeAuthenticatedAction($request, fn(AppointmentSummaryRequest $req) =>
            $appointmentService->checkAppointed(PsoContext::fromRequest($req))
        );
    }

    /**
     * Get Appointments
     */
    #[SentToPso(data: 'array{appointmentOffers: array{appointmentRequestId: string, summary: string, bestOffer: array<string, mixed>|string, validOffers: list<array<string, mixed>>, invalidOffers: list<array<string, mixed>>, allOfferValues: list<array<string, mixed>>}}', examples: [[
        'appointmentOffers' => [
            'appointmentRequestId' => 'abc123',
            'summary' => '1 valid offers out of 2 returned.',
            'bestOffer' => ['id' => '1', 'windowStartDatetime' => '2025-05-29T08:00:00', 'windowEndDatetime' => '2025-05-29T12:00:00', 'offerValue' => '100', 'prospectiveResourceId' => 'RES-001', 'windowDayEnglish' => 'Thu, May 29, 2025', 'windowStartTime' => '8:00 AM', 'windowEndTime' => '12:00 PM'],
            'validOffers' => [['id' => '1', 'offerValue' => '100', 'prospectiveResourceId' => 'RES-001', 'isBestOffer' => true]],
            'invalidOffers' => [['id' => '2', 'offerValue' => '0']],
            'allOfferValues' => [['id' => '1', 'offerValue' => '100'], ['id' => '2', 'offerValue' => '0']],
        ],
    ]])]
    #[NotSentToPso(examples: [['payloadToPso' => ['dsScheduleData' => ['@xmlns' => 'http://360Scheduling.com/Schema/dsScheduleData.xsd', 'Appointment_Request' => ['id' => 'abc123', 'activity_id' => 'ACT-001_AB'], 'Activity' => ['id' => 'ACT-001_AB']]]]])]
    public function store(AppointmentRequest $request, AppointmentService $appointmentService): JsonResponse
    {
        return $this->executeAuthenticatedAction($request, fn(AppointmentRequest $req) =>
            $appointmentService->getAppointment(PsoContext::fromRequest($req))
        );
    }

    /**
     * Get Appointment Details
     */
    #[OkResponse(data: 'AppointmentResource')]
    public function show(PSOAppointment $appointmentRequestId): JsonResponse
    {
        return $this->ok(new AppointmentResource($appointmentRequestId));
    }

    /**
     * Accept Appointment
     */
    #[SentToPso(data: 'array{acceptedAppointmentSummary: array{appointmentRequestId: string, activityId: string, resourceId: string, assignmentStart: string, assignmentFinish: string, pso_allocation: string, selectedDate: string, selectedWindow: string}, payloadToPso: object}', examples: [[
        'acceptedAppointmentSummary' => ['appointmentRequestId' => 'abc123', 'activityId' => 'ACT-001_AB', 'resourceId' => 'RES-001', 'assignmentStart' => '2025-05-29T08:00:00-04:00', 'assignmentFinish' => '2025-05-29T09:00:00-04:00', 'pso_allocation' => 'psoAllocation', 'selectedDate' => 'Thu, May 29, 2025', 'selectedWindow' => '8:00 AM - 12:00 PM'],
        'payloadToPso' => ['dsScheduleData' => ['@xmlns' => 'http://360Scheduling.com/Schema/dsScheduleData.xsd', 'Appointment_Offer_Response' => ['appointment_request_id' => 'abc123', 'appointment_offer_id' => '1', 'accept' => true]]],
    ]])]
    #[NotSentToPso]
    public function update(AppointmentSummaryRequest $request, AppointmentService $appointmentService): JsonResponse
    {
        return $this->executeAuthenticatedAction($request, fn(AppointmentSummaryRequest $req) =>
            $appointmentService->acceptAppointment(PsoContext::fromRequest($req))
        );
    }

    /**
     * Decline Appointment
     */
    #[SentToPso(data: 'array{declineAppointmentSummary: array{activityId: string, appointmentRequestId: string, declinedOffers: int, totalAppointmentsOffered: int}, payloadToPso: object}', examples: [[
        'declineAppointmentSummary' => ['activityId' => 'ACT-001_AB', 'appointmentRequestId' => 'abc123', 'declinedOffers' => 1, 'totalAppointmentsOffered' => 2],
        'payloadToPso' => ['dsScheduleData' => ['@xmlns' => 'http://360Scheduling.com/Schema/dsScheduleData.xsd', 'Appointment_Offer_Response' => ['appointment_request_id' => 'abc123', 'appointment_offer_id' => '1']]],
    ]])]
    #[NotSentToPso]
    public function destroy(AppointmentSummaryRequest $request, AppointmentService $appointmentService): JsonResponse
    {
        return $this->executeAuthenticatedAction($request, fn(AppointmentSummaryRequest $req) =>
            $appointmentService->declineAppointment(PsoContext::fromRequest($req))
        );
    }
}

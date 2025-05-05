<?php

namespace App\Services\V2;


use App\Classes\V2\BaseService;
use App\Enums\AppointmentRequestStatus;
use App\Enums\PsoEndpointSegment;
use App\Helpers\Stubs\AppointmentOfferResponse;
use App\Helpers\Stubs\AppointmentRequest;
use App\Models\PSOAppointment;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use JsonException;
use Ramsey\Uuid\Uuid;
use SensitiveParameter;

class AppointmentService extends BaseService
{


    public function __construct(#[SensitiveParameter] string|null $sessionToken = null, $data)
    {
        parent::__construct($sessionToken, $data);
    }


    /**
     * Get appointment offers from PSO
     *
     * @return JsonResponse
     */
    public function getAppointment(): JsonResponse
    {

        $runId = Uuid::uuid4()->toString();

        try {
            $payload = AppointmentRequest::make($this->data);

            $environmentData = data_get($this->data, 'environment');
            $timezone = data_get($this->data, 'timezone');

            if ($this->sessionToken) {

                $this->createAppointmentRecord($runId, $this->data, $payload);
                $psoPayload = $this->buildPayload($payload);

                $psoResponse = $this->sendToPso(
                    $psoPayload,
                    $environmentData,
                    $this->sessionToken,
                    PsoEndpointSegment::APPOINTMENT
                );

                // Check if response is successful (status code < 400)
                if ($psoResponse->status() < 400) {
                    $offers = $this->collectAndFormatAppointmentResponses($psoResponse, $timezone);

                    $this->updateAppointmentRequestWithOffers($runId, $offers, $psoResponse);

                    return $this->sentToPso($offers);
                }

                // If there was an error, just return the error response
                return $psoResponse;
            }

            return $this->notSentToPso($this->buildPayload($payload, 1, true));
        } catch (Exception $e) {
            $this->LogError($e, __METHOD__, __CLASS__);
            return $this->error('An unexpected error occurred', 500);
        }
    }


    public function acceptAppointment(): JsonResponse
    {
        try {

            $appointmentRequestId = data_get($this->data, 'data.appointmentRequestId');
            $appointmentOfferId = data_get($this->data, 'data.appointmentOfferId');
            $environmentData = data_get($this->data, 'environment');

            $payload = AppointmentOfferResponse::make($appointmentRequestId, $appointmentOfferId, true);
            $inputReferenceId = data_get($payload, 'Input_Reference.id');


            try {
                $acceptOffer = $this->updateAppointmentRequestAcceptOrDeclineOffer($appointmentRequestId, $appointmentOfferId, $inputReferenceId);
                if (data_get($acceptOffer, 'status') !== 200) {
                    $this->error(data_get($acceptOffer, 'message'), data_get($acceptOffer, 'status'));
                }

            } catch (ModelNotFoundException) {
                return $this->error('Appointment Request ID was not found', 404);
            }


            // need to get sla_start and sla_end from the offer sent in
            // get the prospective resource ID
            // format the window for readable format

            // build the activity payload again (maybe take it from appointment request or store it correctly)

            // delete the old activity


            if ($this->sessionToken) {
                $psoPayload = $this->buildPayload($payload);

                $psoResponse = $this->sendToPso(
                    $psoPayload,
                    $environmentData,
                    $this->sessionToken,
                    PsoEndpointSegment::APPOINTMENT
                );

                // Check if response is successful (status code < 400)
                if ($psoResponse->status() < 400) {
                    $summary = [ // todo setup this summary
                        'activity_id' => 'activityId',
                        'resource_id' => 'resourceId',
                        'assignment_start' => 'assignmentStart',
                        'assignment_finish' => 'assignmentFinish',
                        'pso_allocation' => 'psoAllocation',
                        'selected_date' => 'selectedDate',
                        'selected_window' => 'selectedWindow',
                    ];
                    return $this->sentToPso($summary, $this->buildPayload($payload, 1, true));
                }

                // If there was an error, just return the error response
                return $psoResponse;
            }


            return $this->notSentToPso($this->buildPayload($payload, 1, true));
        } catch (Exception $e) {
            $this->LogError($e, __METHOD__, __CLASS__);

            return $this->error('An unexpected error occurred', 500);
        }
    }

    private function deleteActivity(string $activityId, array $environmentData, #[SensitiveParameter] string $sessionToken): void
    {

        // expected that environment.sendToPso is always true
        $deleteServicePayload = [
            'environment' => $environmentData,
            'data' => [
                'object_type' => 'activity',
                'object_pk1' => $activityId,
            ]
        ];

        $deleteService = new DeleteService($sessionToken, $deleteServicePayload);
        $deleteService->deleteObject();


    }

    public function declineAppointment(): JsonResponse
    {
        try {

            $appointmentRequestId = data_get($this->data, 'data.appointmentRequestId');
            $environmentData = data_get($this->data, 'environment');

            $payload = AppointmentOfferResponse::make($appointmentRequestId);
            $inputReferenceId = data_get($payload, 'Input_Reference.id');


            try {
                $declineOffer = $this->updateAppointmentRequestAcceptOrDeclineOffer($appointmentRequestId, -1, $inputReferenceId, false);
                if (data_get($declineOffer, 'status') !== 200) {
                    $this->error(data_get($declineOffer, 'message'), data_get($declineOffer, 'status'));
                }

            } catch (ModelNotFoundException) {
                return $this->error('Appointment Request ID was not found', 404);
            }

            $appointmentRequest = PSOAppointment::where('appointment_request', $appointmentRequestId)->first();
            $activityId = data_get($appointmentRequest, 'activity_id');

            if ($this->sessionToken) {
                $psoPayload = $this->buildPayload($payload);

                // send the decline appointment
                $psoResponse = $this->sendToPso(
                    $psoPayload,
                    $environmentData,
                    $this->sessionToken,
                    PsoEndpointSegment::APPOINTMENT
                );

                // Check if response is successful (status code < 400)
                if ($psoResponse->status() < 400) {

                    // delete the activity
                    $this->deleteActivity($activityId, $environmentData, $this->sessionToken);

                    $summary = [ // todo setup this summary
                        'declined X number of appointments. Removed temporary activity'
                    ];
                    return $this->sentToPso($summary, $this->buildPayload($payload, 1, true));
                }

                // If there was an error, just return the error response
                return $psoResponse;
            }


            return $this->notSentToPso($this->buildPayload($payload, 1, true));
        } catch (Exception $e) {
            $this->LogError($e, __METHOD__, __CLASS__);
            return $this->error('An unexpected error occurred', 500);
        }
    }


    public function checkAppointed(): JsonResponse
    {
        try {

            $appointmentRequestId = data_get($this->data, 'data.appointmentRequestId');
            $appointmentOfferId = data_get($this->data, 'data.appointmentOfferId');
            $environmentData = data_get($this->data, 'environment');

            $payload = AppointmentOfferResponse::make($appointmentRequestId, $appointmentOfferId);
            $inputReferenceId = data_get($payload, 'Input_Reference.id');

            //  see if it exists in the DB and ✅
            // is not already checked ✅
            // appointment request is not complete ✅
            // is not accepted or declined ✅
            // is not expired ✅
            // offer is in list of available offers (if not then status 406) ✅

            // I think this whole block neds to be instead of the if statement

            try {
                $checkAppointed = $this->updateAppointmentRequestCheckAppointed($appointmentRequestId, $appointmentOfferId, $inputReferenceId);
                if (data_get($checkAppointed, 'status') !== 200) {
                    $this->error(data_get($checkAppointed, 'message'), data_get($checkAppointed, 'status'));
                }

            } catch (ModelNotFoundException) {
                return $this->error('Appointment Request ID was not found', 404);
            }


            if ($this->sessionToken) {
                $psoPayload = $this->buildPayload($payload);

                $psoResponse = $this->sendToPso(
                    $psoPayload,
                    $environmentData,
                    $this->sessionToken,
                    PsoEndpointSegment::APPOINTMENT
                );

                // Check if response is successful (status code < 400)
                if ($psoResponse->status() < 400) {
                    $summary = collect(data_get(collect($psoResponse->getData())->first(), 'Appointment_Summary', []));
                    $this->updateAppointmentRequestAppointedSummary($appointmentRequestId, $summary);
                    $additional_data = [
                        'appointment_summary' => [
                            'appointment_request_id' => $appointmentRequestId,
                            'slot_is_available' => data_get($summary, 'appointed'),
                            'full_summary' => $summary
                        ]
                    ];
                    return $this->sentToPso($additional_data, $this->buildPayload($payload, 1, true));
                }

                // If there was an error, just return the error response
                return $psoResponse;
            }


            return $this->notSentToPso($this->buildPayload($payload, 1, true));
        } catch (Exception $e) {
            $this->LogError($e, __METHOD__, __CLASS__);
            return $this->error('An unexpected error occurred', 500);
        }
    }

    /**
     * Collect and format appointment responses
     *
     * @param JsonResponse $response The response containing appointment offers
     * @param string|null $timezone Timezone for date formatting
     * @return array Formatted appointment data
     */
    private function collectAndFormatAppointmentResponses(JsonResponse $response, string|null $timezone = null): array
    {
        // Extract offers from response
        $offers = collect(data_get(collect($response->getData())->first(), 'Appointment_Offer', []));
        $appointmentRequestId = data_get($offers->first(), 'appointment_request_id');

        // Find best offer
        $bestOfferValue = $offers->max(static function ($offer) {
            return (float)data_get($offer, 'offer_value', 0);
        });

        $bestOffer = $offers
            ->where('offer_value', $bestOfferValue)
            ->map(fn($offer) => $this->getPut($offer, null, $timezone))
            ->first();

        // Collect valid offers (offer_value not equal to "0")
        $validOffers = $offers
            ->filter(static fn($offer) => data_get($offer, 'offer_value') !== "0")
            ->map(fn($offer) => $this->getPut($offer, data_get($bestOffer, 'id'), $timezone))
            ->values();

        // Collect invalid offers (offer_value equal to "0")
        $invalidOffers = $offers
            ->filter(static fn($offer) => data_get($offer, 'offer_value') === "0")
            ->map(static function ($offer) {
                return collect([
                    'id' => data_get($offer, 'id'),
                    'window_start_datetime' => data_get($offer, 'window_start_datetime'),
                    'window_end_datetime' => data_get($offer, 'window_end_datetime'),
                    'offer_value' => data_get($offer, 'offer_value'),
                ]);
            })
            ->values();

        // Offer values summary
        $offerValues = $offers
            ->map(static function ($offer) {
                return collect([
                    'id' => data_get($offer, 'id'),
                    'offer_value' => data_get($offer, 'offer_value'),
                    'window_start_datetime' => data_get($offer, 'window_start_datetime'),
                    'prospective_resource_id' => data_get($offer, 'prospective_resource_id'),
                ]);
            })
            ->values();

        // Build final data
        return [

            'appointment_offers' => [
                'appointment_request_id' => $appointmentRequestId,
                'summary' => "{$validOffers->count()} valid offers out of {$offers->count()} returned.",
                'best_offer' => data_get($bestOffer, 'prospective_resource_id') ? $bestOffer : 'no valid offers returned',
                'valid_offers' => $validOffers,
                'invalid_offers' => $invalidOffers,
                'offer_values' => $offerValues,
            ],
        ];
    }

    /**
     * Format an offer with additional time-related information
     *
     * @param mixed $offer The offer to format
     * @param string|null $bestOfferId ID of the best offer for comparison
     * @param string|null $timezone Timezone for date formatting
     * @return Collection Formatted offer
     */
    private function getPut(mixed $offer, string|null $bestOfferId = null, string|null $timezone = null): Collection
    {
        $timezone = $timezone ?? (string)(config('pso-services.defaults.timezone', 'America/Toronto'));

        $start = Carbon::parse(data_get($offer, 'window_start_datetime'))->setTimezone($timezone);
        $end = Carbon::parse(data_get($offer, 'window_end_datetime'))->setTimezone($timezone);

        $newcollect = collect([
            'id' => data_get($offer, 'id'),
            'window_start_datetime' => data_get($offer, 'window_start_datetime'),
            'window_end_datetime' => data_get($offer, 'window_end_datetime'),
            'offer_value' => data_get($offer, 'offer_value'),
            'prospective_resource_id' => data_get($offer, 'prospective_resource_id'),
            'prospective_allocation_start' => data_get($offer, 'prospective_allocation_start'),
            'window_start_english' => $start->toDayDateTimeString(),
            'window_end_english' => $end->toDayDateTimeString(),
            'window_day_english' => $start->toFormattedDayDateString(),
            'window_start_time' => $start->format('g:i A'),
            'window_end_time' => $end->format('g:i A')
        ]);

        if ($bestOfferId !== null) {
            $newcollect->put('is_best_offer', data_get($offer, 'id') === $bestOfferId);
        }

        return $newcollect;
    }

    /**
     * @throws JsonException
     */
    public function createAppointmentRecord(string $runId, array $data, array $payload): void
    {
        // Extract common data from the payload
        $appointmentRequest = data_get($payload, 'Appointment_Request');

        $inputRequest = data_get($payload, 'Input_Reference');

        $activityData = Arr::except($payload, ['Input_Reference', 'Appointment_Request']);

        // Create the appointment record
        PSOAppointment::create([
            'run_id' => $runId,
            'appointment_request' => json_encode($payload, JSON_THROW_ON_ERROR),
            'input_request' => json_encode($inputRequest, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES),
            'status' => AppointmentRequestStatus::UNACKNOWLEDGED->value,
            'activity' => json_encode($activityData, JSON_THROW_ON_ERROR),
            'activity_id' => data_get($data, 'data.activityId'),
            'base_url' => data_get($data, 'environment.baseUrl'),
            'dataset_id' => data_get($data, 'environment.datasetId'),
            'input_reference_id' => data_get($inputRequest, 'id'),
            'appointment_template_id' => data_get($appointmentRequest, 'appointment_template_id'),
            'slot_usage_rule_id' => data_get($appointmentRequest, 'slot_usage_rule_set_id'),
            'appointment_template_duration' => data_get($appointmentRequest, 'appointment_template_duration'),
            'appointment_template_datetime' => data_get($appointmentRequest, 'appointment_template_datetime'),
            'offer_expiry_datetime' => Carbon::now()->addMinutes(5)->toAtomString(),
        ]);
    }

    /**
     * @throws JsonException
     */
    private function updateAppointmentRequestWithOffers(string $runId, array $offers, JsonResponse $response): void
    {
        // Extract offer counts using data_get
        $appointmentOffers = data_get($offers, 'appointment_offers', []);

        $offersCount = count(data_get($appointmentOffers, 'offer_values', []));
        $validOffers = data_get($appointmentOffers, 'valid_offers', []);
        $invalidOffers = data_get($appointmentOffers, 'invalid_offers', []);
        $validOffersCount = count($validOffers);
        $invalidOffersCount = count($invalidOffers);

        // Extract and encode necessary data using data_get
        $responseData = json_encode(collect($response->getData())->first(), JSON_THROW_ON_ERROR);
        $validOffersJson = json_encode($validOffers, JSON_THROW_ON_ERROR);
        $invalidOffersJson = json_encode($invalidOffers, JSON_THROW_ON_ERROR);
        $bestOfferJson = json_encode(data_get($appointmentOffers, 'best_offer', []), JSON_THROW_ON_ERROR);
        $summary = data_get($appointmentOffers, 'summary', '');

        // Find the appointment and update it
        $appointmentRequest = PSOAppointment::where('run_id', $runId)->firstOrCreate();
        $appointmentRequest->update([
            'appointment_response' => $responseData,
            'valid_offers' => $validOffersJson,
            'invalid_offers' => $invalidOffersJson,
            'best_offer' => $bestOfferJson,
            'summary' => $summary,
            'total_offers_returned' => $offersCount,
            'total_valid_offers_returned' => $validOffersCount,
            'total_invalid_offers_returned' => $invalidOffersCount,
        ]);
    }


    /**
     * @throws JsonException
     */
    private function updateAppointmentRequestAcceptOrDeclineOffer(string $appointmentRequestId, string $appointmentOfferId, string $inputReferenceId, $accept = true): array|null
    {
        $checkResult = $this->validateAppointmentSummary($appointmentRequestId, $appointmentOfferId);

        // If there's an error, return the response with message and status
        if ($checkResult) {
            return $checkResult; // Return early if validation failed
        }

        $appointmentRequest = PSOAppointment::where('appointment_request', $appointmentRequestId)->firstOrFail();
        $offers = collect($appointmentRequest->valid_offers);
        $appointmentRequest->update([
            'accepted_offer' => json_encode($offers->firstWhere('id', $appointmentOfferId), JSON_THROW_ON_ERROR),
            'accepted_offer_id' => $appointmentOfferId,
            'accepted_offer_datetime' => Carbon::now()->toAtomString(),
            'accept_decline_input_reference_id' => $inputReferenceId,
            'status' => $accept ? AppointmentRequestStatus::ACCEPTED->value : AppointmentRequestStatus::DECLINED->value,
        ]);

        return $checkResult; // Return early if validation failed


    }


    /*
     * this method is called when the user checks the offer
     * the next method will update the record to see if the offer was actually available
     *
     */
    private function updateAppointmentRequestCheckAppointed(string $appointmentRequestId, string $appointmentOfferId, string $inputReferenceId): array|null
    {

        // Call the reusable method to check for validity
        $checkResult = $this->validateAppointmentSummary($appointmentRequestId, $appointmentOfferId);

        // If there's an error, return the response with message and status
        if ($checkResult) {
            return $checkResult; // Return early if validation failed
        }

        // if no error, update the DB with checkAppointed Values
        $appointmentRequest = PSOAppointment::where('appointment_request', $appointmentRequestId)->firstOrFail();
        $appointmentRequest->update([
            'status' => AppointmentRequestStatus::CHECKED->value,
            'appointed_check_offer_id' => $appointmentOfferId,
            'appointed_check_datetime' => Carbon::now()->toAtomString(),
            'appointed_check_input_reference_id' => $inputReferenceId,
        ]);

        return $checkResult; // Return early if validation failed

    }

    /*
    * this method is called after PSO responds with an appointed summary
    * we are guaranteed the appointmentRequest exists at this point
    *
    */
    private function updateAppointmentRequestAppointedSummary(string $appointmentRequestId, Collection $summary): void
    {
        $appointmentRequest = PSOAppointment::where('appointment_request', $appointmentRequestId)->firstOrFail();
        $appointmentRequest->update([
            'appointed_check_result' => data_get($summary, 'appointed'),
            'appointed_check_complete' => true
        ]);

    }

    private function validateAppointmentSummary(string $appointmentRequestId, string $appointmentOfferId): array|null
    {
        try {
            // Attempt to find the appointment request
            $appointmentRequest = PSOAppointment::where('appointment_request', $appointmentRequestId)->firstOrFail();
        } catch (ModelNotFoundException) {
            return [
                'message' => 'Appointment Request ID was not found',
                'status' => 404
            ];
        }

        // Initialize response structure
        $response = [
            'message' => '',
            'status' => 200, // Default status, assuming all checks pass
        ];

        // Check if valid offers exist
        $offersCollection = collect($appointmentRequest->valid_offers);
        if ($offersCollection->isEmpty()) {
            return [
                'message' => 'No valid offers found for appointment request',
                'status' => 406
            ];
        }

        // Check if the specific offer ID exists
        if (!$offersCollection->contains('id', $appointmentOfferId)) {
            return [
                'message' => 'This is not a valid appointment offer ID',
                'status' => 406
            ];
        }

        // Check if the appointment request status is valid
        if (AppointmentRequestStatus::from($appointmentRequest->status) !== AppointmentRequestStatus::UNACKNOWLEDGED) {
            return [
                'message' => 'Appointment Request ID is no longer valid for check',
                'status' => 406
            ];
        }

        // Check if the appointment request has expired
        if ($appointmentRequest->offer_expiry_datetime->isPast()) {
            return [
                'message' => 'Appointment Request has expired',
                'status' => 406
            ];
        }

        // All checks passed, return null (meaning no error)
        return null;
    }


}

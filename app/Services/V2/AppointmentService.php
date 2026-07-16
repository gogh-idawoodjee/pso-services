<?php

namespace App\Services\V2;

use App\Classes\V2\BaseService;
use App\Classes\V2\EntityBuilders\InputReferenceBuilder;
use App\DataTransferObjects\PsoContext;
use App\Enums\AppointmentRequestStatus;
use App\Enums\InputMode;
use App\Enums\PsoEndpointSegment;
use GoghIdawoodjee\ShortCode\Facades\ShortCode;
use App\Helpers\Stubs\AppointmentOfferResponse;
use App\Helpers\Stubs\AppointmentRequest;
use App\Jobs\DeleteTempActivity;
use App\Models\V2\PSOAppointment;
use Carbon\Carbon;
use DateInterval;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use JsonException;
use Ramsey\Uuid\Uuid;

class AppointmentService extends BaseService
{
    /**
     * Get appointment offers from PSO
     *
     * @param PsoContext $context
     * @return JsonResponse
     */
    public function getAppointment(PsoContext $context): JsonResponse
    {
        $runId = Uuid::uuid4()->toString();
        $activitySuffix = ShortCode::encodeUuid($runId);

        try {
            $payload = AppointmentRequest::make($context->validated, $activitySuffix);

            $environmentData = $context->environment();
            $timezone = data_get($context->validated, 'timezone');

            if ($context->token) {
                $this->createAppointmentRecord($runId, $context->validated, $payload, $activitySuffix);
                $psoPayload = $this->psoClient->buildPayload($payload);

                $psoResponse = $this->psoClient->sendToPso(
                    $psoPayload,
                    $environmentData,
                    $context->token,
                    PsoEndpointSegment::APPOINTMENT,
                );

                if ($psoResponse->status() < 400) {
                    $offers = $this->collectAndFormatAppointmentResponses($psoResponse, $timezone);
                    $this->updateAppointmentRequestWithOffers($runId, $offers, $psoResponse);

                    return $this->sentToPso($offers);
                }

                return $psoResponse;
            }

            return $this->notSentToPso($this->psoClient->buildPayload($payload, 1, true));
        } catch (Exception $e) {
            $this->logError($e, __METHOD__, __CLASS__);
            return $this->error('An unexpected error occurred', 500);
        }
    }

    public function acceptAppointment(PsoContext $context): JsonResponse
    {
        try {
            $appointmentRequestId = $context->data('appointmentRequestId');
            $appointmentOfferId = $context->data('appointmentOfferId');
            $environmentData = $context->environment();
            $appointmentRequestLog = PSOAppointment::where('appointment_request_id', $appointmentRequestId)->first();
            $validOffers = collect(data_get($appointmentRequestLog, 'valid_offers'));
            $selectedOffer = $validOffers->firstWhere('id', $appointmentOfferId);
            $activity = data_get($appointmentRequestLog, 'activity'); // the activity JSON
            $suffix = data_get($appointmentRequestLog, 'short_code');
            $activityId = data_get($appointmentRequestLog, 'activity_id');

            $offerResponsePayload = AppointmentOfferResponse::make($appointmentRequestId, $appointmentOfferId, true);

            $inputReference = InputReferenceBuilder::make($context->datasetId())
                ->inputType(InputMode::CHANGE)
                ->description('Accept and Book Appointment for ' . $activityId . ' with appointment request ID ' . $appointmentRequestId)
                ->build();
            // TODO: input reference is created here, not fetched — review naming/flow
            $inputReferenceId = data_get($inputReference, 'id');

            $payload = ['Appointment_Offer_Response' => $offerResponsePayload, 'Input_Reference' => $inputReference];

            $acceptOffer = $this->updateAppointmentRequestAcceptOrDeclineOffer($appointmentRequestId, $appointmentOfferId, $inputReferenceId);
            if (data_get($acceptOffer, 'status') !== 200 && data_get($acceptOffer, 'status')) {
                return $this->error(data_get($acceptOffer, 'message'), data_get($acceptOffer, 'status'));
            }

            // TODO: delete the old activity? Or rely on the background job?
            // Once deleted, set cleanup_datetime to now and required_manual_cleanup to false

            $bookAppointmentPayload = $this->createBookAppointmentPayload(
                $activity,
                $activityId,
                $suffix,
                data_get($selectedOffer, 'windowStartDatetime'),
                data_get($selectedOffer, 'windowEndDatetime')
            );

            $duration = data_get($activity, 'Activity_Status.duration');

            $allocationStart = Carbon::parse(data_get($selectedOffer, 'prospectiveAllocationStart'));
            $allocationFinish = $allocationStart->copy();

            if ($duration) {
                try {
                    $interval = new DateInterval($duration);
                    $allocationFinish->add($interval);
                } catch (Exception $e) {
                    Log::warning('Invalid duration format', [
                        'duration' => $duration,
                        'error' => $e->getMessage()
                    ]);
                    $allocationFinish->addHour();
                }
            }

            $payload = array_merge($payload, (array)$bookAppointmentPayload);

            if ($context->token) {
                $psoPayload = $this->psoClient->buildPayload($payload);

                $psoResponse = $this->psoClient->sendToPso(
                    $psoPayload,
                    $environmentData,
                    $context->token,
                    PsoEndpointSegment::APPOINTMENT,
                );

                $this->scheduleCleanup($appointmentRequestLog, 3);

                if ($psoResponse->status() < 400) {
                    $summary = [
                        'appointmentRequestId' => $appointmentRequestId,
                        'activityId' => data_get($activity, 'Activity.id'),
                        'resourceId' => data_get($selectedOffer, 'prospectiveResourceId'),
                        'assignmentStart' => $allocationStart->toIso8601String(),
                        'assignmentFinish' => $allocationFinish->toIso8601String(),
                        'pso_allocation' => 'psoAllocation', // TODO: determine what this field should contain
                        'selectedDate' => data_get($selectedOffer, 'windowDayEnglish'),
                        'selectedWindow' => data_get($selectedOffer, 'windowStartTime') . ' - ' . data_get($selectedOffer, 'windowEndTime'),
                    ];
                    return $this->sentToPso([
                        'acceptedAppointmentSummary' => $summary,
                        'payloadToPso' => $this->psoClient->buildPayload($payload, 1, true),
                    ]);
                }

                return $psoResponse;
            }

            return $this->notSentToPso($this->psoClient->buildPayload($payload, 1, true));
        } catch (Exception $e) {
            $this->logError($e, __METHOD__, __CLASS__);
            return $this->error('An unexpected error occurred', 500);
        }
    }

    private function overrideActivitySlaTimestamps(array|object $sla, string $slaStart, string $slaEnd): array|object
    {
        if (is_array($sla)) {
            $sla['datetime_start'] = $slaStart;
            $sla['datetime_end'] = $slaEnd;
        } else {
            $sla->datetime_start = $slaStart;
            $sla->datetime_end = $slaEnd;
        }

        return $sla;
    }


    private function createBookAppointmentPayload($activity, string $activityId, string $suffix, $slaStart, $slaEnd)
    {
        $search = $activityId . $suffix;
        $replace = $activityId;

        if (is_array($activity)) {
            foreach ($activity as $key => $value) {
                if ($key === 'Activity_SLA' && is_array($value)) {
                    $value = $this->overrideActivitySlaTimestamps($value, $slaStart, $slaEnd);
                }

                $activity[$key] = $this->createBookAppointmentPayload($value, $activityId, $suffix, $slaStart, $slaEnd);
            }
        } elseif (is_object($activity)) {
            foreach ($activity as $key => $value) {
                if ($key === 'Activity_SLA' && is_object($value)) {
                    $value = $this->overrideActivitySlaTimestamps($value, $slaStart, $slaEnd);
                }

                $activity->$key = $this->createBookAppointmentPayload($value, $activityId, $suffix, $slaStart, $slaEnd);
            }
        } elseif (is_string($activity)) {
            if ($activity === $search) {
                return $replace;
            }

            if (str_contains($activity, $search)) {
                return Str::replace($search, $replace, $activity);
            }
        }

        return $activity;
    }

    private function deleteActivity(string $activityId, array $environmentData, string $token): void
    {
        $context = new PsoContext(
            token: $token,
            validated: [
                'environment' => $environmentData,
                'data' => [
                    'objectType' => 'activity',
                    'objectPk1' => $activityId,
                ],
            ],
        );

        app(DeleteService::class)->deleteObject($context);
    }

    public function declineAppointment(PsoContext $context): JsonResponse
    {
        try {
            $appointmentRequestId = $context->data('appointmentRequestId');
            $environmentData = $context->environment();

            $offerResponsePayload = AppointmentOfferResponse::make($appointmentRequestId);

            $inputReference = InputReferenceBuilder::make($context->datasetId())->inputType(InputMode::CHANGE)->build();
            // TODO: input reference is created here, not fetched — review naming/flow
            $inputReferenceId = data_get($inputReference, 'id');

            $payload = ['Appointment_Offer_Response' => $offerResponsePayload, 'Input_Reference' => $inputReference];

            $declineOffer = $this->updateAppointmentRequestAcceptOrDeclineOffer($appointmentRequestId, -1, $inputReferenceId, false);

            if (data_get($declineOffer, 'status') !== 200 && data_get($declineOffer, 'status')) {
                return $this->error(data_get($declineOffer, 'message'), data_get($declineOffer, 'status'));
            }

            $appointmentRequestLog = PSOAppointment::where('appointment_request_id', $appointmentRequestId)->first();
            $validOffers = data_get($appointmentRequestLog, 'total_valid_offers_returned');
            $invalidOffers = data_get($appointmentRequestLog, 'total_invalid_offers_returned');
            $activityId = data_get($appointmentRequestLog, 'activity_id');

            // TODO: delete the old activity
            // Once deleted, set cleanup_datetime to now and required_manual_cleanup to false
            $this->scheduleCleanup($appointmentRequestLog, 3);

            if ($context->token) {
                $psoPayload = $this->psoClient->buildPayload($payload);

                $psoResponse = $this->psoClient->sendToPso(
                    $psoPayload,
                    $environmentData,
                    $context->token,
                    PsoEndpointSegment::APPOINTMENT
                );

                if ($psoResponse->status() < 400) {
                    $this->deleteActivity($activityId, $environmentData, $context->token);

                    $summary = [
                        'activityId' => $activityId,
                        'appointmentRequestId' => $appointmentRequestId,
                        'declinedOffers' => $validOffers,
                        'totalAppointmentsOffered' => $validOffers + $invalidOffers,
                    ];
                    return $this->sentToPso([
                        'declineAppointmentSummary' => $summary,
                        'payloadToPso' => $this->psoClient->buildPayload($payload, 1, true),
                    ]);
                }

                return $psoResponse;
            }

            return $this->notSentToPso($this->psoClient->buildPayload($payload, 1, true));
        } catch (Exception $e) {
            $this->logError($e, __METHOD__, __CLASS__);
            return $this->error('An unexpected error occurred', 500);
        }
    }

    public function checkAppointed(PsoContext $context): JsonResponse
    {
        try {
            $appointmentRequestId = $context->data('appointmentRequestId');
            $appointmentOfferId = $context->data('appointmentOfferId');
            $environmentData = $context->environment();

            Log::info('checkAppointed started', compact('appointmentRequestId', 'appointmentOfferId'));

            $offerResponsePayload = AppointmentOfferResponse::make($appointmentRequestId, $appointmentOfferId);

            $inputReference = InputReferenceBuilder::make($context->datasetId())
                ->inputType(InputMode::CHANGE)
                ->build();
            $inputReferenceId = data_get($inputReference, 'id');

            $payload = [
                'Appointment_Offer_Response' => $offerResponsePayload,
                'Input_Reference' => $inputReference
            ];

            $checkAppointed = $this->updateAppointmentRequestCheckAppointed(
                $appointmentRequestId,
                $appointmentOfferId,
                $inputReferenceId,
                (bool)$context->token
            );

            if (data_get($checkAppointed, 'status') !== 200 && data_get($checkAppointed, 'status')) {
                Log::warning('checkAppointed failed appointment check', [
                    'status' => data_get($checkAppointed, 'status'),
                    'message' => data_get($checkAppointed, 'message'),
                ]);
                return $this->error(data_get($checkAppointed, 'message'), data_get($checkAppointed, 'status'));
            }

            if ($context->token) {
                $psoPayload = $this->psoClient->buildPayload($payload);

                $psoResponse = $this->psoClient->sendToPso(
                    $psoPayload,
                    $environmentData,
                    $context->token,
                    PsoEndpointSegment::APPOINTMENT
                );

                if ($psoResponse->status() < 400) {
                    $summary = collect(data_get(collect($psoResponse->getData())->first(), 'Appointment_Summary', []));
                    $this->updateAppointmentRequestAppointedSummary($appointmentRequestId, $summary);

                    $data = [
                        'appointment_summary' => [
                            'appointmentRequestId' => $appointmentRequestId,
                            'isSlotAvailable' => data_get($summary, 'appointed'),
                            'responseFromPso' => $summary
                        ]
                    ];

                    Log::info('checkAppointed successful', compact('appointmentRequestId'));
                    return $this->sentToPso(array_merge($data, [
                        'payloadToPso' => $this->psoClient->buildPayload($payload, 1, true),
                    ]));
                }

                Log::warning('checkAppointed: PSO responded with an error', ['status' => $psoResponse->status()]);
                return $psoResponse;
            }

            Log::info('checkAppointed skipped PSO send (no session token)', compact('appointmentRequestId'));
            return $this->notSentToPso($this->psoClient->buildPayload($payload, 1, true));
        } catch (Exception $e) {
            Log::error('Exception caught in checkAppointed()', [
                'message' => $e->getMessage(),
                'method' => __METHOD__,
            ]);
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
            ->map(fn($offer) => $this->formatAppointmentOffer($offer, null, $timezone))
            ->first();

        // Collect valid offers (offer_value not equal to "0")
        $validOffers = $offers
            ->filter(static fn($offer) => data_get($offer, 'offer_value') !== "0")
            ->map(fn($offer) => $this->formatAppointmentOffer($offer, data_get($bestOffer, 'id'), $timezone))
            ->values();

        // Collect invalid offers (offer_value equal to "0")
        $invalidOffers = $offers
            ->filter(static fn($offer) => data_get($offer, 'offer_value') === "0")
            ->map(static function ($offer) {
                return collect([
                    'id' => data_get($offer, 'id'),
                    'windowStartDatetime' => data_get($offer, 'window_start_datetime'),
                    'windowEndDatetime' => data_get($offer, 'window_end_datetime'),
                    'offerValue' => data_get($offer, 'offer_value'),
                ]);
            })
            ->values();

        // Offer values summary
        $offerValues = $offers
            ->map(static function ($offer) {
                return collect([
                    'id' => data_get($offer, 'id'),
                    'offerValue' => data_get($offer, 'offer_value'),
                    'windowStartDatetime' => data_get($offer, 'window_start_datetime'),
                    'windowEndDatetime' => data_get($offer, 'window_end_datetime'),
                    'prospectiveResourceId' => data_get($offer, 'prospective_resource_id'),
                ]);
            })
            ->values();

        // Build final data
        return [

            'appointmentOffers' => [
                'appointmentRequestId' => $appointmentRequestId,
                'summary' => "{$validOffers->count()} valid offers out of {$offers->count()} returned.",
                'bestOffer' => data_get($bestOffer, 'prospectiveResourceId') ? $bestOffer : 'no valid offers returned',
                'validOffers' => $validOffers,
                'invalidOffers' => $invalidOffers,
                'allOfferValues' => $offerValues,
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
    private function formatAppointmentOffer(mixed $offer, string|null $bestOfferId = null, string|null $timezone = null): Collection
    {
        $timezone = $timezone ?? (string)(config('pso-services.defaults.timezone', 'America/Toronto'));

        $start = Carbon::parse(data_get($offer, 'window_start_datetime'))->setTimezone($timezone);
        $end = Carbon::parse(data_get($offer, 'window_end_datetime'))->setTimezone($timezone);

        $summary = collect([
            'id' => data_get($offer, 'id'),
            'windowStartDatetime' => data_get($offer, 'window_start_datetime'),
            'windowEndDatetime' => data_get($offer, 'window_end_datetime'),
            'offerValue' => data_get($offer, 'offer_value'),
            'prospectiveResourceId' => data_get($offer, 'prospective_resource_id'),
            'prospectiveAllocationStart' => data_get($offer, 'prospective_allocation_start'),
            'windowStartEnglish' => $start->toDayDateTimeString(),
            'windowEndEnglish' => $end->toDayDateTimeString(),
            'windowDayEnglish' => $start->toFormattedDayDateString(),
            'windowStartTime' => $start->format('g:i A'),
            'windowEndTime' => $end->format('g:i A')
        ]);

        if ($bestOfferId !== null) {
            $summary->put('isBestOffer', data_get($offer, 'id') === $bestOfferId);
        }

        return $summary;
    }

    /**
     * @throws JsonException
     */
    private function createAppointmentRecord(string $runId, array $data, array $payload, string $suffix): void
    {
        $data = $this->encryptSensitiveEnvironmentFields($data);

        // Extract common data from the payload
        $appointmentRequest = data_get($payload, 'Appointment_Request');

        $inputRequest = data_get($payload, 'Input_Reference');

        $activityData = Arr::except($payload, ['Input_Reference', 'Appointment_Request']);

        // Create the appointment record
        PSOAppointment::create([
            'run_id' => $runId,
            'short_code' => $suffix,
            'service_api_input' => json_encode($data, JSON_THROW_ON_ERROR),
            'appointment_request_id' => data_get($appointmentRequest, 'id'),
            'appointment_request' => json_encode($payload, JSON_THROW_ON_ERROR),
            'input_request' => json_encode($inputRequest, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES),
            'status' => AppointmentRequestStatus::UNACKNOWLEDGED->value,
            'activity' => json_encode($activityData, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES),
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

    private function encryptSensitiveEnvironmentFields(array $data): array
    {
        if (!isset($data['environment'])) {
            return $data;
        }

        $sensitiveKeys = ['username', 'password', 'token'];

        foreach ($sensitiveKeys as $key) {
            if (!empty($data['environment'][$key])) {
                $data['environment'][$key] = Crypt::encryptString($data['environment'][$key]);
            }
        }

        return $data;
    }

    /**
     * @throws JsonException
     */
    private function updateAppointmentRequestWithOffers(string $runId, array $offers, JsonResponse $response): void
    {
        // Extract offer counts using data_get
        $appointmentOffers = data_get($offers, 'appointmentOffers', []);

        $offersCount = count(data_get($appointmentOffers, 'allOfferValues', []));
        $validOffers = data_get($appointmentOffers, 'validOffers', []);
        $invalidOffers = data_get($appointmentOffers, 'invalidOffers', []);
        $validOffersCount = count($validOffers);
        $invalidOffersCount = count($invalidOffers);

        // Extract and encode necessary data using data_get
        $responseData = json_encode(collect($response->getData())->first(), JSON_THROW_ON_ERROR);
        $validOffersJson = json_encode($validOffers, JSON_THROW_ON_ERROR);
        $invalidOffersJson = json_encode($invalidOffers, JSON_THROW_ON_ERROR);
        $bestOfferJson = json_encode(data_get($appointmentOffers, 'bestOffer', []), JSON_THROW_ON_ERROR);
        $summary = data_get($appointmentOffers, 'summary', '');

        // Find the appointment and update it
        $appointmentRequest = PSOAppointment::where('run_id', $runId)->firstOrFail();
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
        $checkResult = $this->validateAppointmentSummary($appointmentRequestId, $appointmentOfferId, $accept);

        if ($checkResult) {
            return $checkResult;
        }

        $appointmentRequest = PSOAppointment::where('appointment_request_id', $appointmentRequestId)->firstOrFail();

        $offers = collect($appointmentRequest->valid_offers);
        $appointmentRequest->update([
            'accepted_offer' => json_encode($offers->firstWhere('id', $appointmentOfferId), JSON_THROW_ON_ERROR),
            'accepted_offer_id' => $appointmentOfferId,
            'accept_decline_datetime' => Carbon::now()->toAtomString(),
            'accept_decline_input_reference_id' => $inputReferenceId,
            'status' => $accept ? AppointmentRequestStatus::ACCEPTED->value : AppointmentRequestStatus::DECLINED->value,
        ]);

        return null;
    }

    /*
     * this method is called when the user checks the offer
     * the next method will update the record to see if the offer was actually available
     *
     */
    private function updateAppointmentRequestCheckAppointed(string $appointmentRequestId, string $appointmentOfferId, string $inputReferenceId, bool|null $sendToPso = null): array|null
    {
        Log::info('starting updateAppointmentRequestCheckAppointed');

        $checkResult = $this->validateAppointmentSummary($appointmentRequestId, $appointmentOfferId);

        if ($checkResult) {
            Log::info('found an error in updateAppointmentRequestCheckAppointed');
            return $checkResult;
        }

        // Only update DB if sendToPSO is true
        if ($sendToPso) {
            $appointmentRequest = PSOAppointment::where('appointment_request_id', $appointmentRequestId)->firstOrFail();
            $appointmentRequest->update([
                'status' => AppointmentRequestStatus::CHECKED->value,
                'appointed_check_offer_id' => $appointmentOfferId,
                'appointed_check_datetime' => Carbon::now()->toAtomString(),
                'appointed_check_input_reference_id' => $inputReferenceId,
            ]);
        }

        return null;
    }

    /*
    * this method is called after PSO responds with an appointed summary
    * we are guaranteed the appointmentRequest exists at this point
    *
    */
    private function updateAppointmentRequestAppointedSummary(string $appointmentRequestId, Collection $summary): void
    {
        Log::debug('Looking for PSOAppointment with appointment_request', [
            'appointment_request_id' => $appointmentRequestId
        ]);

        $appointmentRequest = PSOAppointment::where('appointment_request_id', $appointmentRequestId)->firstOrFail();

        $appointmentRequest->update([
            'appointed_check_result' => (bool)data_get($summary, 'appointed'),
            'appointed_check_complete' => true
        ]);
    }

    private function validateAppointmentSummary(string $appointmentRequestId, string $appointmentOfferId, bool|null $isAcceptRequest = true): array|null
    {
        $appointmentRequest = PSOAppointment::where('appointment_request_id', $appointmentRequestId)->first();

        if (!$appointmentRequest) {
            Log::warning('Appointment request not found', compact('appointmentRequestId'));
            return [
                'message' => 'Appointment Request ID was not found',
                'status' => 404
            ];
        }

        $appointmentRequestStatus = AppointmentRequestStatus::from($appointmentRequest->status);

        $offersCollection = collect($appointmentRequest->valid_offers);

        // applies to accept, decline and check
        if ($offersCollection->isEmpty()) {
            Log::warning('No valid offers found for appointment request', compact('appointmentRequestId'));
            return [
                'message' => 'No valid offers found for appointment request',
                'status' => 406
            ];
        }

        // applies only to accept
        if ($isAcceptRequest && !$offersCollection->contains('id', $appointmentOfferId)) {
            Log::warning('Invalid appointment offer ID', [
                'appointmentOfferId' => $appointmentOfferId,
                'valid_ids' => $offersCollection->pluck('id')->all(),
            ]);
            return [
                'message' => 'This is not a valid appointment offer ID',
                'status' => 406
            ];
        }

        // For accept requests: only allow UNACKNOWLEDGED or CHECKED status
        // For decline/check requests: only allow UNACKNOWLEDGED status
        $validStatuses = $isAcceptRequest
            ? [AppointmentRequestStatus::UNACKNOWLEDGED, AppointmentRequestStatus::CHECKED]
            : [AppointmentRequestStatus::UNACKNOWLEDGED];

        if (!in_array($appointmentRequestStatus, $validStatuses, true)) {
            Log::warning('Appointment request is no longer in a valid status', ['status' => $appointmentRequest->status]);
            return [
                'message' => 'Appointment Request ID is no longer valid for ' . ($isAcceptRequest ? 'accepting' : 'check'),
                'status' => 406
            ];
        }

        if ($appointmentRequest->offer_expiry_datetime->isPast()) {
            Log::warning('Appointment request has expired', ['expiry' => $appointmentRequest->offer_expiry_datetime]);
            return [
                'message' => 'Appointment Request has expired',
                'status' => 406
            ];
        }

        // All good
        return null;
    }

    private function scheduleCleanup(PSOAppointment $appointmentRequestLog, int|null $timeout = null): void
    {
        if (!$timeout) {
            $timeout = config('pso-services.defaults.travel_broadcast_timeout_minutes');
        }
        DeleteTempActivity::dispatch($appointmentRequestLog)
            ->delay(now()->addMinutes($timeout));
    }

}

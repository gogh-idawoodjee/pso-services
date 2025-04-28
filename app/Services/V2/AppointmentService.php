<?php

namespace App\Services\V2;

use App\Classes\V2\BaseService;
use App\Enums\PsoEndpointSegment;
use App\Helpers\Stubs\AppointmentRequest;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use JsonException;
use SensitiveParameter;

class AppointmentService extends BaseService
{
    protected array $data;


    public function __construct(#[SensitiveParameter] string|null $sessionToken = null, array $data)
    {

        parent::__construct($sessionToken);
        $this->data = $data;
        // extract our data?


    }

    /**
     * Get appointment offers from PSO
     *
     * @return JsonResponse
     */
    public function getAppointment(): JsonResponse
    {
        try {
            $payload = AppointmentRequest::make($this->data);
            $environmentData = data_get($this->data, 'environment');
            $timezone = data_get($this->data, 'timezone');

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
                    $offers = $this->collectAndFormatAppointmentResponses($psoResponse, $timezone);
                    return $this->ok($offers);
                }

                // If there was an error, just return the error response
                return $psoResponse;
            }

            return $this->notSentToPso($this->buildPayload($payload, 1, true));
        } catch (Exception $e) {
            Log::error('Unexpected error in getAppointment: ' . $e->getMessage());
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
            'description' => 'appointment_offers',
            'data' => [
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


}

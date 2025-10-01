<?php

namespace App\Services\V2;

use App\Classes\V2\BaseService;
use App\Classes\V2\EntityBuilders\BroadcastBuilder;
use App\Classes\V2\EntityBuilders\BroadcastParameterBuilder;
use App\Enums\BroadcastAllocationType;
use App\Enums\BroadcastParameterType;
use App\Enums\BroadcastPlanType;
use App\Enums\TravelLogStatus;
use App\Helpers\Stubs\TravelDetailRequest;
use App\Jobs\TravelLogReview;
use App\Models\V2\PSOTravelLog;
use App\Traits\V2\PSOAssistV2;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use JsonException;
use Log;
use Ramsey\Uuid\Uuid;
use SensitiveParameter;
use Spatie\Geocoder\Geocoder;

class TravelService extends BaseService
{
    use PSOAssistV2;

    protected array $data;
    private string $travelLogId;
    private string|null $datasetId;
    protected array $warnings = [];
    protected bool $hasGoogleKey = false;
    protected bool $hasGeocoderKey = false;

    /**
     */
    public function __construct(#[SensitiveParameter] string|null $sessionToken = null, array $data)
    {
        parent::__construct($sessionToken, $data);
        $this->travelLogId = Uuid::uuid4()->toString();

        // Check if keys exist (either in config or request data)
        $this->hasGoogleKey = !empty(config('pso-services.settings.google_key'))
            || !empty(data_get($this->data, 'data.googleApiKey'));
        $this->hasGeocoderKey = !empty(config('geocoder.key'));

        // Add warnings if keys are missing
        if (!$this->hasGoogleKey) {
            $this->warnings[] = 'Google Maps API key is not configured. Distance calculations may be unavailable.';
        }
        if (!$this->hasGeocoderKey) {
            $this->warnings[] = 'Geocoder API key is not configured. Address resolution may be unavailable.';
        }
    }

    /**
     * Process the travel request and return the response
     *
     * @return JsonResponse Response data
     * @throws JsonException
     */
    public function process(): JsonResponse
    {
        // Step 1: Build payload
        $payload = TravelDetailRequest::make(
            $this->travelLogId,
            data_get($this->data, 'data.latFrom'),
            data_get($this->data, 'data.longFrom'),
            data_get($this->data, 'data.latTo'),
            data_get($this->data, 'data.longTo'),
            data_get($this->data, 'data.travelProfileId'),
            data_get($this->data, 'data.startDateTime'),
        );

        // Step 2: Resolve addresses and distance (with error handling)
        [$startAddress, $endAddress, $addressErrors] = $this->getAddresses();
        if (!empty($addressErrors)) {
            $this->warnings = array_merge($this->warnings, $addressErrors);
        }

        $googleResults = $this->getDistanceMatrix(
            data_get($this->data, 'data.latFrom'),
            data_get($this->data, 'data.longFrom'),
            data_get($this->data, 'data.latTo'),
            data_get($this->data, 'data.longTo'),
            data_get($this->data, 'data.googleApiKey')
        );

        // Check if distance matrix failed
        // Only add this warning if we had a key but it still failed
        // (the specific error will already be in warnings from getDistanceMatrix)
        if ($googleResults === null && $this->hasGoogleKey && !$this->hasWarning(
                'Google Distance Matrix'
            ) && !$this->hasWarning('Google API')) {
            $this->warnings[] = 'Google Distance Matrix API request failed. Distance/duration data unavailable.';
        }

        // Step 3: Create travel log
        $travelLog = PSOTravelLog::create([
            'id' => $this->travelLogId,
            'status' => TravelLogStatus::CREATED,
            'address_from' => $this->encodeJson($startAddress),
            'address_to' => $this->encodeJson($endAddress),
            'google_response' => $this->encodeJson($googleResults),
        ]);

        // Step 4: Build broadcast structure
        $broadcast = $this->buildBroadcast();

        // Step 5: Send payload
        $additionalDetails = $this->getAdditionalDetails();
        $apiResponse = $this->sendOrSimulateBuilder()
            ->payload(['Travel_Detail_Request' => $payload] + $broadcast)
            ->environment(data_get($this->data, 'environment'))
            ->token($this->sessionToken)
            ->includeInputReference('Travel Detail Request: ' . $this->travelLogId)
            ->additionalDetails($additionalDetails['message'])
            ->resultsUrl($additionalDetails['url'])
            ->send();

        // Step 6: Update travel log with PSO response
        $responseArray = $apiResponse->getData(true);

        $travelLog->update([
            'input_reference' => $this->travelLogId,
            'input_payload' => $this->encodeJson(data_get($responseArray, 'data.payloadToPso')),
            'output_payload' => $this->encodeJson(data_get($responseArray, 'data.responseFromPso')),
            'status' => TravelLogStatus::SENT,
        ]);

        // check the log after 2 minutes
        TravelLogReview::dispatch($travelLog)->delay(
            now()->addMinutes(config('pso-services.defaults.travel_broadcast_timeout_minutes'))
        );

        return $apiResponse;
    }

    protected function getAddresses(): array
    {
        $latFrom = data_get($this->data, 'data.latFrom');
        $longFrom = data_get($this->data, 'data.longFrom');
        $latTo = data_get($this->data, 'data.latTo');
        $longTo = data_get($this->data, 'data.longTo');

        $errors = [];

        // Try to geocode, but handle failures gracefully
        try {
            $start = $this->hasGeocoderKey
                ? $this->reverseGeocode($latFrom, $longFrom)
                : ['address' => null, 'accuracy' => null, 'error' => 'No geocoder key configured'];
        } catch (Exception $e) {
            $start = ['address' => null, 'accuracy' => null, 'error' => $e->getMessage()];
            $errors[] = 'Failed to geocode start address: ' . $e->getMessage();
        }

        try {
            $end = $this->hasGeocoderKey
                ? $this->reverseGeocode($latTo, $longTo)
                : ['address' => null, 'accuracy' => null, 'error' => 'No geocoder key configured'];
        } catch (Exception $e) {
            $end = ['address' => null, 'accuracy' => null, 'error' => $e->getMessage()];
            $errors[] = 'Failed to geocode end address: ' . $e->getMessage();
        }

        return [$start, $end, $errors];
    }

    /**
     * @throws JsonException
     */
    protected function encodeJson(mixed $data): string
    {
        return json_encode($data, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
    }

    protected function getAdditionalDetails(): array
    {
        $message = '';
        $url = null;

        if ($this->sessionToken) {
            $url = route('travel.analyzer.show', ['id' => $this->travelLogId]);
            $message = "To review results, please send a GET request to {$url}";
        } else {
            $message = "Please ensure environment.sendToPso is set to true to use the analyzer correctly";
        }

        // Append warnings if any exist
        if (!empty($this->warnings)) {
            $warningText = implode(' | ', $this->warnings);
            $message .= " | Warnings: {$warningText}";
        }

        return compact('message', 'url');
    }

    protected function buildBroadcast(): array
    {
        return BroadcastBuilder::make()
            ->allocationType(BroadcastAllocationType::SCHEDULING_TRAVEL_ANALYSER)
            ->parameters([
                BroadcastParameterBuilder::make()
                    ->name(BroadcastParameterType::MEDIATYPE)
                    ->value('application/json'),

                BroadcastParameterBuilder::make()
                    ->name(BroadcastParameterType::URL)
                    ->value(route('travelanalyzer.update')),
            ])
            ->type('REST')
            ->onceOnly()
            ->planType(BroadcastPlanType::COMPLETE)
            ->build();
    }

    /**
     * @throws JsonException
     */
    public function receivePSOBroadcast(): JsonResponse
    {
        $travelDetails = data_get($this->data, 'Travel_Detail', []);

        foreach ($travelDetails as $detail) {
            PSOTravelLog::where('id', data_get($detail, 'travel_detail_request_id'))
                ->update([
                    'pso_response' => json_encode($detail, JSON_THROW_ON_ERROR),
                    'status' => TravelLogStatus::COMPLETED,
                ]);
        }

        return response()->json([
            'status' => 204,
            'description' => 'all good'
        ], 204, ['Content-Type', 'application/json'], JSON_UNESCAPED_SLASHES);
    }

    /**
     */
    protected function getDistanceMatrix(
        float $latFrom,
        float $longFrom,
        float $latTo,
        float $longTo,
        string|null $apiKey = null
    ): array|null {
        // Determine which key to use: provided key, or config key
        $keyToUse = $apiKey ?? config('pso-services.settings.google_key');

        // If no key is available at all, return null early
        if (empty($keyToUse)) {
            return null;
        }

        $query = [
            'origins' => "{$latTo},{$longTo}",
            'destinations' => "{$latFrom},{$longFrom}",
            'key' => $keyToUse,
        ];

        try {
            $response = Http::timeout(5)
                ->connectTimeout(5)
                ->acceptJson()
                ->get('https://maps.googleapis.com/maps/api/distancematrix/json', $query);

            if ($response->failed()) {
                // Log the failure for debugging
                Log::warning('Google Distance Matrix API failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                // Add a more specific warning about the failure
                $this->warnings[] = 'Google Distance Matrix API request failed with status ' . $response->status(
                    ) . '. Check API key validity.';
                return null;
            }

            $result = data_get($response->json(), 'rows.0.elements.0');

            // Check if the result indicates an error (like invalid API key)
            if (isset($result['status']) && $result['status'] !== 'OK') {
                Log::warning('Google Distance Matrix returned error status', [
                    'status' => $result['status'],
                    'result' => $result,
                ]);

                // Add specific warning based on the error status
                $errorMessage = match ($result['status']) {
                    'REQUEST_DENIED' => 'Google API key is invalid or request was denied. Please check your API key and permissions.',
                    'OVER_QUERY_LIMIT' => 'Google API query limit exceeded. Please check your quota.',
                    'ZERO_RESULTS' => 'No route found between the specified locations.',
                    default => 'Google Distance Matrix API error: ' . $result['status']
                };

                $this->warnings[] = $errorMessage;
                return null;
            }

            return $result;
        } catch (Exception $e) {
            Log::error('Exception calling Google Distance Matrix API', [
                'message' => $e->getMessage(),
            ]);

            $this->warnings[] = 'Exception calling Google API: ' . $e->getMessage();
            return null;
        }
    }

    protected function reverseGeocode(float $lat, float $long): array
    {
        $geocoder = (new Geocoder(new Client()))
            ->setApiKey(config('geocoder.key'));

        $address = $geocoder->getAddressForCoordinates($lat, $long);

        return [
            'address' => $address['formatted_address'] ?? null,
            'accuracy' => $address['accuracy'] ?? null,
        ];
    }

    /**
     * Check if a warning message already exists (partial match)
     */
    protected function hasWarning(string $search): bool
    {
        foreach ($this->warnings as $warning) {
            if (str_contains($warning, $search)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @throws JsonException
     */
    public function getTravelResults(PSOTravelLog $travelLog): array
    {
        if ($travelLog->status === TravelLogStatus::COMPLETED) {
            return [
                'travel_detail_request_id' => $travelLog->travel_detail_request_id,
                'start_address' => $travelLog->getAddressFromTextAttribute(),
                'end_address' => $travelLog->getAddressToTextAttribute(),
                'pso' => [
                    'time' => $travelLog->getPsoTimeFormattedAttribute(),
                    'distance' => $travelLog->getDistanceInKmAttribute(),
                ],
                'google' => [
                    'time' => $travelLog->getGoogleDurationAttribute(),
                    'distance' => $travelLog->getGoogleDistanceAttribute(),
                ]
            ];
        }
        return [];
    }
}

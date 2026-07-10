<?php

namespace App\Services\V2;

use App\Classes\V2\BaseService;
use App\Classes\V2\EntityBuilders\BroadcastBuilder;
use App\Classes\V2\EntityBuilders\BroadcastParameterBuilder;
use App\DataTransferObjects\PsoContext;
use App\Enums\BroadcastAllocationType;
use App\Enums\BroadcastParameterType;
use App\Enums\BroadcastPlanType;
use App\Enums\TravelLogStatus;
use App\Helpers\Stubs\TravelDetailRequest;
use App\Jobs\DispatchTravelCallback;
use App\Jobs\TravelLogReview;
use App\Models\V2\PSOTravelLog;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use JsonException;
use Log;
use Ramsey\Uuid\Uuid;
use Spatie\Geocoder\Geocoder;

class TravelService extends BaseService
{
    protected array $warnings = [];
    protected bool $hasGoogleKey = false;
    protected bool $hasGeocoderKey = false;

    /**
     * @throws JsonException
     */
    public function process(PsoContext $context): JsonResponse
    {
        $travelLogId = Uuid::uuid4()->toString();

        Log::withContext(['travelLogId' => $travelLogId, 'endpoint' => 'travelanalyzer']);
        Log::info('Travel analyzer request received', [
            'hasCallbackUrl' => !empty($context->data('callbackUrl')),
            'hasGoogleApiKey' => !empty($context->data('googleApiKey')),
            'travelProfileId' => $context->data('travelProfileId'),
            'sendToPso' => $context->environment()['sendToPso'] ?? null,
            'psoApiVersion' => $context->psoApiVersion(),
        ]);

        // Check API key availability
        $this->hasGoogleKey = !empty(config('pso-services.settings.google_key'))
            || !empty($context->data('googleApiKey'));
        $this->hasGeocoderKey = !empty(config('geocoder.key'));

        if (!$this->hasGoogleKey) {
            $this->warnings[] = 'Google Maps API key is not configured. Distance calculations may be unavailable.';
        }
        if (!$this->hasGeocoderKey) {
            $this->warnings[] = 'Geocoder API key is not configured. Address resolution may be unavailable.';
        }

        // Step 1: Build payload
        $payload = TravelDetailRequest::make(
            $travelLogId,
            $context->data('latFrom'),
            $context->data('longFrom'),
            $context->data('latTo'),
            $context->data('longTo'),
            $context->data('travelProfileId'),
            $context->data('startDateTime'),
        );
        Log::info('Travel detail payload built', ['payload' => $payload]);

        // Step 2: Resolve addresses and distance
        [$startAddress, $endAddress, $addressErrors] = $this->getAddresses($context);
        if (!empty($addressErrors)) {
            $this->warnings = array_merge($this->warnings, $addressErrors);
        }
        Log::info('Address resolution complete', [
            'startAddress' => $startAddress['address'] ?? null,
            'endAddress' => $endAddress['address'] ?? null,
            'addressErrors' => $addressErrors,
        ]);

        $googleResults = $this->getDistanceMatrix(
            $context->data('latFrom'),
            $context->data('longFrom'),
            $context->data('latTo'),
            $context->data('longTo'),
            $context->data('googleApiKey'),
        );

        if ($googleResults === null && $this->hasGoogleKey && !$this->hasWarning('Google Distance Matrix') && !$this->hasWarning('Google API')) {
            $this->warnings[] = 'Google Distance Matrix API request failed. Distance/duration data unavailable.';
        }
        Log::info('Google distance matrix complete', ['result' => $googleResults]);

        // Step 3: Create travel log
        $travelLog = PSOTravelLog::create([
            'id' => $travelLogId,
            'status' => TravelLogStatus::CREATED,
            'address_from' => $this->encodeJson($startAddress),
            'address_to' => $this->encodeJson($endAddress),
            'google_response' => $this->encodeJson($googleResults),
            'warnings' => !empty($this->warnings) ? $this->encodeJson($this->warnings) : null,
            'callback_url' => $context->data('callbackUrl'),
        ]);
        Log::info('Travel log record created', ['status' => $travelLog->status->value]);

        // Step 4: Build broadcast structure
        $broadcast = $this->buildBroadcast();

        // Step 5: Send payload
        $additionalDetails = $this->getAdditionalDetails($context->token, $travelLogId);

        $psoPayload = ['Travel_Detail_Request' => $payload] + $broadcast;
        Log::info('Sending payload to PSO', ['baseUrl' => $context->baseUrl(), 'payload' => $psoPayload]);

        try {
            $apiResponse = $this->psoClient->sendOrSimulateBuilder()
                ->payload($psoPayload)
                ->environment($context->environment())
                ->psoApiVersion($context->psoApiVersion())
                ->token($context->token)
                ->includeInputReference('Travel Detail Request: ' . $travelLogId)
                ->additionalDetails($additionalDetails['message'])
                ->resultsUrl($additionalDetails['url'])
                ->send();
        } catch (\Throwable $e) {
            Log::error('Sending travel payload to PSO failed', [
                'exceptionClass' => get_class($e),
                'exceptionMessage' => $e->getMessage(),
            ]);
            throw $e;
        }

        // Step 6: Update travel log with PSO response
        $responseArray = $apiResponse->getData(true);
        Log::info('PSO response received', ['response' => $responseArray]);

        $travelLog->update([
            'input_reference' => $travelLogId,
            'input_payload' => $this->encodeJson(data_get($responseArray, 'data.payloadToPso')),
            'output_payload' => $this->encodeJson(data_get($responseArray, 'data.responseFromPso')),
            'status' => TravelLogStatus::SENT,
        ]);

        $timeoutMinutes = config('pso-services.defaults.travel_broadcast_timeout_minutes');
        TravelLogReview::dispatch($travelLog)->delay(now()->addMinutes($timeoutMinutes));
        Log::info('Travel log sent; TravelLogReview job scheduled', ['delayMinutes' => $timeoutMinutes]);

        return $apiResponse;
    }

    protected function getAddresses(PsoContext $context): array
    {
        $latFrom = $context->data('latFrom');
        $longFrom = $context->data('longFrom');
        $latTo = $context->data('latTo');
        $longTo = $context->data('longTo');

        $errors = [];

        try {
            $start = $this->hasGeocoderKey
                ? $this->reverseGeocode($latFrom, $longFrom)
                : ['address' => null, 'accuracy' => null, 'error' => 'No geocoder key configured'];
        } catch (Exception $e) {
            $start = ['address' => null, 'accuracy' => null, 'error' => $e->getMessage()];
            $errors[] = 'Failed to geocode start address: ' . $e->getMessage();
            Log::error('Failed to geocode start address', [
                'latFrom' => $latFrom,
                'longFrom' => $longFrom,
                'exceptionClass' => get_class($e),
                'exceptionMessage' => $e->getMessage(),
            ]);
        }

        try {
            $end = $this->hasGeocoderKey
                ? $this->reverseGeocode($latTo, $longTo)
                : ['address' => null, 'accuracy' => null, 'error' => 'No geocoder key configured'];
        } catch (Exception $e) {
            $end = ['address' => null, 'accuracy' => null, 'error' => $e->getMessage()];
            $errors[] = 'Failed to geocode end address: ' . $e->getMessage();
            Log::error('Failed to geocode end address', [
                'latTo' => $latTo,
                'longTo' => $longTo,
                'exceptionClass' => get_class($e),
                'exceptionMessage' => $e->getMessage(),
            ]);
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

    protected function getAdditionalDetails(string|null $token, string $travelLogId): array
    {
        $message = '';
        $url = null;

        if ($token) {
            $url = route('v2.travel.show', ['id' => $travelLogId]);
            $message = "To review results, please send a GET request to {$url}";
        } else {
            $message = 'Please ensure environment.sendToPso is set to true to use the analyzer correctly';
        }

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
                    ->value(route('v2.travel.update')),
            ])
            ->type('REST')
            ->onceOnly()
            ->planType(BroadcastPlanType::COMPLETE)
            ->build();
    }

    /**
     * @throws JsonException
     */
    public function receivePSOBroadcast(array $data): JsonResponse
    {
        $travelDetails = data_get($data, 'Travel_Detail', []);
        Log::info('Travel PSO broadcast received', ['count' => count($travelDetails), 'travelDetails' => $travelDetails]);

        foreach ($travelDetails as $detail) {
            $travelLogId = data_get($detail, 'travel_detail_request_id');
            Log::withContext(['travelLogId' => $travelLogId, 'endpoint' => 'travelanalyzerservice']);

            $travelLog = PSOTravelLog::find($travelLogId);

            if (!$travelLog) {
                Log::warning('Travel PSO broadcast referenced unknown travelLogId', ['detail' => $detail]);
                continue;
            }

            $travelLog->update([
                'pso_response' => json_encode($detail, JSON_THROW_ON_ERROR),
                'status' => TravelLogStatus::COMPLETED,
            ]);
            Log::info('Travel log marked COMPLETED from PSO broadcast', ['detail' => $detail]);

            if ($travelLog->callback_url) {
                DispatchTravelCallback::dispatch($travelLog);
                Log::info('DispatchTravelCallback job queued', ['callbackUrl' => $travelLog->callback_url]);
            }
        }

        return response()->json([
            'status' => 204,
            'description' => 'all good',
        ], 204, ['Content-Type', 'application/json'], JSON_UNESCAPED_SLASHES);
    }

    protected function getDistanceMatrix(float $latFrom, float $longFrom, float $latTo, float $longTo, string|null $apiKey = null): array|null
    {
        $passthrough = config('pso-services.settings.google_api_passthrough');
        if ($passthrough && $apiKey === $passthrough) {
            $apiKey = config('pso-services.settings.google_key');
        }

        $keyToUse = $apiKey ?? config('pso-services.settings.google_key');

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
                Log::warning('Google Distance Matrix API failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                $this->warnings[] = 'Google Distance Matrix API request failed with status ' . $response->status() . '. Check API key validity.';
                return null;
            }

            $result = data_get($response->json(), 'rows.0.elements.0');

            if (isset($result['status']) && $result['status'] !== 'OK') {
                Log::warning('Google Distance Matrix returned error status', [
                    'status' => $result['status'],
                    'result' => $result,
                ]);

                $errorMessage = match ($result['status']) {
                    'REQUEST_DENIED' => 'Google API key is invalid or request was denied. Please check your API key and permissions.',
                    'OVER_QUERY_LIMIT' => 'Google API query limit exceeded. Please check your quota.',
                    'ZERO_RESULTS' => 'No route found between the specified locations.',
                    default => 'Google Distance Matrix API error: ' . $result['status'],
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
            $result = [
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
                ],
            ];

            if (!empty($travelLog->warnings)) {
                try {
                    $result['warnings'] = json_decode($travelLog->warnings, true, 512, JSON_THROW_ON_ERROR);
                } catch (JsonException) {
                    $result['warnings'] = ['Failed to parse warnings'];
                }
            }

            return $result;
        }

        return [];
    }
}

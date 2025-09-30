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
use GuzzleHttp\Client;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use JsonException;
use Ramsey\Uuid\Uuid;
use SensitiveParameter;
use Spatie\Geocoder\Geocoder;

class TravelService extends BaseService
{
    use PSOAssistV2;

    protected array $data;
    private string $travelLogId;
    private string|null $datasetId;

    /**
     */
    public function __construct(#[SensitiveParameter] string|null $sessionToken = null, array $data)
    {
        parent::__construct($sessionToken, $data);
        $this->travelLogId = Uuid::uuid4()->toString();
    }

    /**
     * Process the travel request and return the response
     *
     * @return JsonResponse Response data
     * @throws JsonException|ConnectionException
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

        // Step 2: Resolve addresses and distance
        [$startAddress, $endAddress] = $this->getAddresses();
        $googleResults = $this->getDistanceMatrix(
            data_get($this->data, 'data.latFrom'),
            data_get($this->data, 'data.longFrom'),
            data_get($this->data, 'data.latTo'),
            data_get($this->data, 'data.longTo')
        );

//        dd($googleResults);

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

        // Step 5: Send payload or simulate
        $additionalDetails = $this->getAdditionalDetails();
// Step 5: Send payload or simulate
        $details = $this->getAdditionalDetails();
        $apiResponse = $this->sendOrSimulateBuilder()
            ->payload(['Travel_Detail_Request' => $payload] + $broadcast)
            ->environment(data_get($this->data, 'environment'))
            ->token($this->sessionToken)
            ->includeInputReference('Travel Detail Request: ' . $this->travelLogId)
            ->additionalDetails($additionalDetails['message'])  // Pass just the message string
            ->resultsUrl($details['url'])  // Pass just the URL string
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
        TravelLogReview::dispatch($travelLog)->delay(now()->addMinutes(config('pso-services.defaults.travel_broadcast_timeout_minutes')));

        return $apiResponse;
    }


    protected function getAddresses(): array
    {
        $latFrom = data_get($this->data, 'data.latFrom');
        $longFrom = data_get($this->data, 'data.longFrom');
        $latTo = data_get($this->data, 'data.latTo');
        $longTo = data_get($this->data, 'data.longTo');

        $start = $this->reverseGeocode($latFrom, $longFrom);
        $end = $this->reverseGeocode($latTo, $longTo);

        return [$start, $end];
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
        if ($this->sessionToken) {
            $url = route('travel.analyzer.show', ['id' => $this->travelLogId]);
            return [
                'message' => "To review results, please send a GET request to {$url}",
                'url' => $url
            ];
        }
        return [
            'message' => "Please ensure environment.sendToPso is set to true to use the analyzer correctly",
            'url' => null
        ];
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
//                    ->value('https://webhook.site/fa0e00f3-91df-486d-b20e-fa8cd4309fe0'),
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
     * @throws ConnectionException
     */
    protected function getDistanceMatrix(float $latFrom, float $longFrom, float $latTo, float $longTo, string|null $apiKey = null): array|null
    {
        $apiKey = $apiKey ?? (string)config('pso-services.settings.google_key');


        $query = [
            'origins' => "{$latTo},{$longTo}",
            'destinations' => "{$latFrom},{$longFrom}",
            'key' => $apiKey,
        ];

        $response = Http::timeout(5)
            ->connectTimeout(5)
            ->acceptJson()
            ->get('https://maps.googleapis.com/maps/api/distancematrix/json', $query);

        if ($response->failed()) {
            // Optional: log or throw exception
            return null;
        }

        return data_get($response->json(), 'rows.0.elements.0');
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
     * @throws JsonException
     */
    public function getTravelResults(PSOTravelLog $travelLog): array
    {

//        dd(data_get(json_decode($travelLog->pso_response), 'travel_detail_request_id'));
        if ($travelLog->status === TravelLogStatus::COMPLETED) {

            return
                [
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

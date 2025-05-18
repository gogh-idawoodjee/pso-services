<?php

namespace App\Services\V2;

use App\Classes\V2\BaseService;
use App\Classes\V2\EntityBuilders\BroadcastBuilder;
use App\Classes\V2\EntityBuilders\BroadcastParameterBuilder;
use App\Classes\V2\EntityBuilders\InputReferenceBuilder;
use App\Enums\BroadcastAllocationType;
use App\Enums\BroadcastParameterType;
use App\Enums\BroadcastPlanType;
use App\Enums\InputMode;
use App\Helpers\Stubs\Broadcast;
use App\Helpers\Stubs\TravelDetailRequest;
use App\Models\PSOTravelLog;
use App\Traits\V2\PSOAssistV2;
use Illuminate\Http\JsonResponse;
use JsonException;
use Ramsey\Uuid\Uuid;
use SensitiveParameter;

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
     * @throws JsonException
     */
    public function process(): JsonResponse
    {

        // 1) receive PSO creds + coords - done
        // 2) send to PSO - done
        // 3) broadcast back to second endpoint
        // 4) stuff gets stored
        // 5) stuff gets returned

        // Create travel log
        $payload = TravelDetailRequest::make(
            $this->travelLogId,
            data_get($this->data, 'data.latFrom'),
            data_get($this->data, 'data.longFrom'),
            data_get($this->data, 'data.latTo'),
            data_get($this->data, 'data.longTo'),
            data_get($this->data, 'data.travelProfileId'),
            data_get($this->data, 'data.startDateTime'),
        );


        $travelLog = PSOTravelLog::create([
            'id' => $this->travelLogId,
            'status' => 'created' //TODO consider changing this to enum
        ]);


        $broadcast = BroadcastBuilder::make()
            ->allocationType(BroadcastAllocationType::SCHEDULING_TRAVEL_ANALYSER)
            ->parameters([
                BroadcastParameterBuilder::make()
                    ->name(BroadcastParameterType::MEDIATYPE)
                    ->value('application/json'),

                BroadcastParameterBuilder::make()
                    ->name(BroadcastParameterType::URL)
                    ->value('https://webhook.site/fa0e00f3-91df-486d-b20e-fa8cd4309fe0'),
            ])
            ->type('REST')
            ->onceOnly()
            ->planType(BroadcastPlanType::COMPLETE)
            ->build();

        if ($this->sessionToken) {
            $additionalDetails = "Please send a GET request to " . route('travel.analyzer.show', ['id' => $this->travelLogId]);
        } else {
            $additionalDetails = "Please ensure environment.sendToPso is set to true to get use the analyzer correctly";
        }


        // Send the payload to the API
        $apiResponse = $this->sendOrSimulateBuilder()
            ->payload(array_merge(
                ['Travel_Detail_Request' => $payload],
                $broadcast
            ))
            ->environment(data_get($this->data, 'environment'))
            ->token($this->sessionToken)
            ->includeInputReference('Travel Detail Request: ' . $this->travelLogId)
            ->additionalDetails($additionalDetails)
            ->send();

        $responseArray = $apiResponse->getData(true);


//        return $this->ok($broadcast);

        $travelLog->update(
            [
                'input_reference' => $this->travelLogId,
                'input_payload' => json_encode(data_get($responseArray, 'data.payloadToPso'), JSON_THROW_ON_ERROR),
                'pso_response' => json_encode(data_get($responseArray, 'data.responseFromPso'), JSON_THROW_ON_ERROR),
                'status' => 'sent'
            ]
        );


        return $apiResponse;
        // $response = $this->sendTravelRequest($payload);

        // TODO: Update travel log with response
        // this would happen in the receiving service
        // $this->updateTravelLog($response);

        // TODO: Return processed response

    }

    private function travelPayload(): array
    {
        $broadcastParameters = [
            [
                'parameter_name' => 'mediatype',
                'parameter_value' => 'application/json'
            ],
            [
                'parameter_name' => 'url',
                // TODO ADD THE URL HERE
                'parameter_value' => 'URL_TO_OUR_TRAVEL_BROADCAST_RECEIVER_API_HERE'
            ]
        ];

        $input_reference = InputReferenceBuilder::make($this->datasetId)->inputType(InputMode::CHANGE)->build();

        $broadcast = Broadcast::make(
            BroadcastAllocationType::SCHEDULING_TRAVEL_ANALYSER,
            $broadcastParameters
        );

        $travel_details = $this->travelDetailRequest($this->data, $this->travelLogId);

        return $this->buildPayload([
            'Input_Reference' => $input_reference,
            'Broadcast' => $broadcast['broadcast_details'],
            'Broadcast_Parameter' => $broadcast['Broadcast_Parameter'],
            'Travel_Details' => $travel_details,
        ]);
    }


    private function travelDetailRequest(array $data, string $id): array
    {
        return [
            'id' => $id,
            'latitude_from' => $data['lat_from'],
            'latitude_to' => $data['lat_to'],
            'longitude_from' => $data['long_from'],
            'longitude_to' => $data['long_to']
        ];
    }

    // TODO: Add method to send the travel request
    // private function sendTravelRequest(array $payload)

    // TODO: Add method to update travel log with response
    // private function updateTravelLog($response)

}

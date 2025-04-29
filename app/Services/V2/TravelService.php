<?php

namespace App\Services\V2;

use App\Classes\V2\BaseService;
use App\Enums\BroadcastAllocationType;
use App\Enums\InputMode;
use App\Helpers\Stubs\Broadcast;
use App\Helpers\Stubs\InputReference;
use App\Models\PSOTravelLog;
use App\Traits\V2\PSOAssistV2;
use Illuminate\Http\Request;
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
    public function __construct(#[SensitiveParameter] string|null $sessionToken = null, Request $request)
    {

        parent::__construct($sessionToken);
        $this->data = $request->get('data');
        $this->datasetId = $request->input('environment.dataset_id');
        $this->travelLogId = Uuid::uuid4()->toString();
    }

    /**
     * Process the travel request and return the response
     *
     * @return array Response data
     * @throws JsonException
     */
    public function process(): array
    {
        // Create travel log
        $payload = $this->travelPayload();
        $travelLog = $this->createTravelLog($payload, $this->travelLogId);

        // TODO: Send the payload to the API
        // $response = $this->sendTravelRequest($payload);

        // TODO: Update travel log with response
        // this would happen in the receiving service
        // $this->updateTravelLog($response);

        // TODO: Return processed response
        return [
            'id' => $this->travelLogId,
            'input_to_pso' => json_decode($travelLog->input_payload, false, 512, JSON_THROW_ON_ERROR),
            'status' => 'pending'
            // 'expiry' => $response['expiry_date'] ?? null
        ];
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

        $input_reference = InputReference::make(
            'Travel Analysis from SERVICE_NAME_HERE',
            InputMode::CHANGE->value,
            $this->datasetId
        );

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

    /**
     * @throws JsonException
     */
    private function createTravelLog(array $payload, string $id): PSOTravelLog
    {
        return PSOTravelLog::create([
            'id' => $id,
            'input_payload' => json_encode($payload, JSON_THROW_ON_ERROR),
            'status' => 'pending' //TODO consider changing this to enum
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

<?php

namespace App\Services\V1;

use App\Classes\V1\InputReference;
use App\Helpers\PSOHelper;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;


/**
 *  V1 — scheduled for removal. Use V2 equivalent.
 */
class IFSPSOAssistService extends IFSService
{

    private function SourceDataParameter($rota_id)
    {
        return
            [
                'source_data_type_id' => "RAM",
                'sequence' => 1,
                'parameter_name' => 'rota_id',
                'parameter_value' => (string)$rota_id,
            ];
    }

    private function RotaToDSEPayload($dataset_id, $rota_id, $datetime, $include_broadcast, $broadcast_type, $broadcast_url, $desc): array
    {
        if (!$desc) {
            $desc = "Update Rota from " . $this->service_name;
        }
        $input_reference = (new InputReference(
            $desc,
            'CHANGE',
            $dataset_id,
            $datetime)
        )->toJson();


        $rota_to_dse_payload = collect([
            'dsScheduleData' => [
                '@xmlns' => 'http://360Scheduling.com/Schema/dsScheduleData.xsd',
                'Input_Reference' => $input_reference,
                'Source_Data' => $this->SourceData(),
                'Source_Data_Parameter' => $this->SourceDataParameter(PSOHelper::RotaID($dataset_id, $rota_id)),
            ]
        ]);

        if ($include_broadcast) {
            $broadcast_payload = $this->BroadcastPayload($broadcast_type, $broadcast_url);
            $rota_to_dse_payload = collect($rota_to_dse_payload->first())->merge(['Broadcast' => $broadcast_payload['Broadcast']]);
            $rota_to_dse_payload = $rota_to_dse_payload->merge(['Broadcast_Parameter' => $broadcast_payload['Broadcast_Parameter']]);
            return ['dsScheduleData' => [$rota_to_dse_payload]];
        }

        return $rota_to_dse_payload->toArray();


    }

    public function sendRotaToDSE($dataset_id, $rota_id, $base_url, $date = null, $send_to_pso = null, $include_broadcast = null, $broadcast_type = null, $broadcast_url = null, $desc200 = null)//: JsonResponse
    {

        $payload = $this->RotaToDSEPayload($dataset_id, $rota_id, $date, $include_broadcast, $broadcast_type, $broadcast_url, $desc200);

        return $this->processPayload($send_to_pso, $payload, $this->token, $base_url);

    }

    public function apiResponse($code, $description, $payload, $payload_desc = null, $additional_data = null): JsonResponse
    {
        // all other services will call this method for payloads
        if ($additional_data) {
            return response()->json([
                'status' => $code,
                'description' => $description,
                $additional_data['description'] => $additional_data['data'],
                $payload_desc ?: 'original_payload' => [$payload]
            ], $code, ['Content-Type', 'application/json'], JSON_UNESCAPED_SLASHES);
        }
        return response()->json([
            'status' => $code,
            'description' => $description,
            $payload_desc ?: 'original_payload' => [$payload]
        ], $code, ['Content-Type', 'application/json'], JSON_UNESCAPED_SLASHES);
    }

    private function SourceData(): array
    {
        return
            [
                'source_data_type_id' => "RAM",
                'sequence' => 1,
            ];
    }

    public function BroadcastPayload($broadcast_type, $broadcast_url)
    {
        $broadcast_id = Str::orderedUuid()->getHex()->toString();
        return [
            'Broadcast' => [
                'active' => true,
                'allocation_type' => $broadcast_type ?: 8,
                'broadcast_type_id' => 'REST',
                'id' => $broadcast_id,
                'once_only' => false,
                'plan_type' => 'COMPLETE'
            ],
            'Broadcast_Parameter' => [
                [
                    'broadcast_id' => $broadcast_id,
                    'parameter_name' => 'mediatype',
                    'parameter_value' => 'application/json'
                ],
                [
                    'broadcast_id' => $broadcast_id,
                    'parameter_name' => 'url',
                    'parameter_value' => $broadcast_url
                ]
            ],
        ];
    }

    public function sendPayloadToPSO($payload, $token, $base_url, $requires_pso_response = false)
    {
        $endpoint_segment = $requires_pso_response ? 'appointment' : 'data';

        try {
            return Http::timeout(PSOHelper::GetTimeOut())
                ->withHeaders(['apiKey' => $token])
                ->connectTimeout(PSOHelper::GetTimeOut())
                ->post($base_url . '/IFSSchedulingRESTfulGateway/api/v1/scheduling/' . $endpoint_segment, $payload);
        } catch (ConnectionException) {
            return response('failed', 500);
        }
    }

    public function processPayload($send_to_pso, $payload, $token, $base_url, $desc_200 = null, $requires_rota_update = false, $dataset_id = null, $rota_id = null)
    {
        if ($send_to_pso) {

            $response = $this->sendPayloadToPSO($payload, $token, $base_url);

            if ($response->json('InternalId') > -1) {
                // update the rota

                if ($requires_rota_update) {
                    $this->sendRotaToDSE(
                        $dataset_id,
                        $rota_id,
                        $base_url,
                        null,
                        true
                    );
                }
                // send the good response
                return $this->apiResponse(200, ("Payload successfully sent to PSO." . ($desc_200 ? ' ' . $desc_200 : $desc_200)), $payload);
            }

            if ($response->serverError() || $response->json('InternalId') === "-1") {
                return $this->apiResponse(500, "Bad data, probably an invalid dataset", $payload);
            }

            if ($response->json('Code') === 401 || $response->status() === 401) {
                return $this->apiResponse(401, "Unable to authenticate with provided token", $payload);
            }

            if ($response->status() === 500) {
                return $this->apiResponse(500, "Probably bad data, payload included for your reference", $payload);
            }
            return $this->apiResponse(418, "None of the above", $payload, null, ['description' => 'PSO Response', 'data' => $response->object()]);
        }

        return $this->apiResponse(202, "Successful but payload not sent to PSO by choice", $payload);

    }
}

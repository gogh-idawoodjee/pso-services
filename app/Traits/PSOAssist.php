<?php

namespace App\Traits;

use App\Helpers\PSOHelper;
use App\Services\IFSPSOAssistService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;

trait PSOAssist
{

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

    public function processPayload($send_to_pso, $payload, $token, $base_url, $desc_200 = null, $requires_rota_update = false, $dataset_id = null, $rota_id = null)
    {
        if ($send_to_pso) {

            $response = $this->sendPayloadToPSO($payload, $token, $base_url);

            if ($response->json('InternalId') > -1) {
                // update the rota
                if ($requires_rota_update) {
                    $this->sendRotaToDSE($dataset_id, $token, null);
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

    private function sendRotaToDSE($base_url, $token, $dataset_id)
    {
        $IFSPSOAssistService = new IFSPSOAssistService($base_url, $token, null, null, null, true);
        $IFSPSOAssistService->sendRotaToDSE($dataset_id, $dataset_id, $base_url);

    }

}

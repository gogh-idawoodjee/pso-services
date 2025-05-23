<?php

namespace App\Traits;

use App\Helpers\PSOHelper;
use App\Services\V1\IFSPSOAssistService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Client\Response as HttpResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

trait PSOAssist
{

    public function apiResponse(
        int     $code,
        string  $description,
                $payload,
        ?string $payload_desc = null,
        ?array  $additional_data = null
    ): JsonResponse
    {
        $response = collect([
            'status' => $code,
            'description' => $description,
            $payload_desc ?: 'original_payload' => [$payload],
        ]);

        if ($additional_data) {
            $response->put(
                Arr::get($additional_data, 'description', 'additional'),
                Arr::get($additional_data, 'data', [])
            );
        }

        return response()->json(
            $response,
            $code,
            ['Content-Type' => 'application/json'],
            JSON_UNESCAPED_SLASHES
        );
    }

    public function processPayload(
        bool $send_to_pso,
             $payload,
        string $token,
        string $base_url,
        ?string $desc_200 = null,
        bool $requires_rota_update = false,
        ?int $dataset_id = null,
        ?int $rota_id = null
    ): JsonResponse {
        if (! $send_to_pso) {
            return $this->apiResponse(202, "Successful but payload not sent to PSO by choice", $payload);
        }

        /** @var Response $response */
        $response = $this->sendPayloadToPSO($payload, $token, $base_url);

        $internalId = $response->json('InternalId');
        $statusCode = $response->status();
        $responseCode = $response->json('Code');

        if ($internalId > -1) {
            if ($requires_rota_update) {
                $this->sendRotaToDSE($dataset_id, $token, null);
            }

            $message = "Payload successfully sent to PSO." . ($desc_200 ? ' ' . $desc_200 : '');
            return $this->apiResponse(200, $message, $payload);
        }

        if ($statusCode === 401 || $responseCode === 401) {
            return $this->apiResponse(401, "Unable to authenticate with provided token", $payload);
        }

        if ($statusCode === 500) {
            return $this->apiResponse(500, "Probably bad data, payload included for your reference", $payload);
        }

        if ($internalId === -1 || $response->serverError()) {
            return $this->apiResponse(500, "Bad data, probably an invalid dataset", $payload);
        }

        return $this->apiResponse(
            418,
            "None of the above",
            $payload,
            null,
            [
                'description' => 'PSO Response',
                'data' => $response->object(),
            ]
        );
    }


    public function sendPayloadToPSO(
        array $payload,
        string $token,
        string $baseUrl,
        bool $requiresPsoResponse = false
    ): ?HttpResponse {
        $endpoint = $requiresPsoResponse ? 'appointment' : 'data';
        $url = Str::finish($baseUrl, '/') . "IFSSchedulingRESTfulGateway/api/v1/scheduling/{$endpoint}";

        try {
            return Http::timeout(PSOHelper::GetTimeOut())
                ->connectTimeout(PSOHelper::GetTimeOut())
                ->withHeaders(['apiKey' => $token])
                ->post($url, $payload);
        } catch (ConnectionException $e) {
            // Log error if needed: Log::error('Connection to PSO failed: ' . $e->getMessage());
            return null; // or return a custom fallback response object
        }
    }

    private function sendRotaToDSE($base_url, $token, $dataset_id)
    {
        $IFSPSOAssistService = new IFSPSOAssistService($base_url, $token, null, null, null, true);
        $IFSPSOAssistService->sendRotaToDSE($dataset_id, $dataset_id, $base_url);

    }

}

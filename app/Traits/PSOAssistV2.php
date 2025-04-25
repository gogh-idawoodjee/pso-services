<?php

namespace App\Traits;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use JsonException;

trait PSOAssistV2
{

    use ApiResponses;

    /**
     * @throws JsonException
     */
    public function getPSOToken(
        array $environment): JsonResponse
    {

        if (isset($environment['token'])) {
            return $this->ok('valid token', $environment['token']);
        }

        try {

            $timeout = config('psott.defaults.timeout', 10);

            // Make the HTTP request
            $response = Http::asForm()
                ->timeout($timeout)
                ->connectTimeout($timeout)
                ->post("{$environment['base_url']}/IFSSchedulingRESTfulGateway/api/v1/scheduling/session", [
                    'accountId' => $environment['account_id'],
                    'userName' => $environment['username'],
                    'password' => $environment['password'],
                ]);

            return $this->handleResponse($response);

        } catch (ConnectionException) {
            // Handle connection issues, like timeouts or network failures
            return response()->json(['error' => 'Connection failed. The request timed out or the server could not be reached.'], 504);

        }
    }

    /**
     * @throws JsonException
     */
    private function handleResponse($response): JsonResponse
    {
        // Check for successful response status
        if ($response->successful()) {
            // Handle success (status 200 or 204)
            return $this->ok('Auth was successful.', $response->json());
        }

        // Handle specific error responses
        return match ($response->status()) {
            400 => response()->json(['error' => 'Client Error. See Details.', 'details' => json_decode($response->body(), false, 512, JSON_THROW_ON_ERROR)], 400),
            401 => response()->json(['error' => 'Unauthorized. Please check your session or login credentials.'], 401),
            404 => response()->json(['error' => 'URL not found. Check the endpoint.'], 404),
            500 => response()->json(['error' => 'Internal server error. Try again later.'], 500),
            default => response()->json(['error' => 'Unexpected error.'], $response->status()),
        };
    }

    public function buildPayload(array $payloadItems, int $psoApiVersion = 1): array
    {
        if ($psoApiVersion === 1) {
            $data = [
                '@xmlns' => 'http://360Scheduling.com/Schema/dsScheduleData.xsd',
            ];

            foreach ($payloadItems as $itemKey => $itemValues) {
                $data[$itemKey] = $itemValues;
            }

            return [
                'dsScheduleData' => $data
            ];
        }

    }


}

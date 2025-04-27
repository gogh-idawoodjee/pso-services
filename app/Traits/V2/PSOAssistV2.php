<?php

namespace App\Traits\V2;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;
use JsonException;

trait PSOAssistV2
{

    use ApiResponses;

    /**
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
                ->post("{$environment['baseUrl']}/IFSSchedulingRESTfulGateway/api/v1/scheduling/session", [
                    'accountId' => $environment['accountId'],
                    'userName' => $environment['username'],
                    'password' => $environment['password'],
                ]);

            return $this->handleResponse($response);

        } catch (ConnectionException) {
            // Handle connection issues, like timeouts or network failures
            return response()->json(['error' => 'Connection failed. The request timed out or the server could not be reached.'], 504);

        }
    }

    private function handleResponse($response): JsonResponse
    {
        if ($response->successful()) {
            $json = $response->json();
            $message = data_get($json, 'SessionToken')
                ? ['SessionToken' => $json['SessionToken']]
                : $json;

            return response()->json([
                'data' => 'Auth was successful.',
                'message' => $message,
                'status' => $response->status(),
            ]);
        }

        $statusCode = $this->adjustStatusCode($response);
        $errorDetails = null;

        if (!empty($response->body())) {
            try {
                $errorDetails = json_decode($response->body(), false, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException) {
                $errorDetails = $response->body();
            }
        }

        return response()->json([
            'error' => match ($statusCode) {
                401 => 'Unauthorized. Please check your session or login credentials.',
                400 => 'Client Error. See Details.',
                404 => 'URL not found. Check the endpoint.',
                500 => 'Internal server error. Try again later.',
                default => 'Unexpected error.',
            },
            'details' => $errorDetails,
        ], $statusCode);
    }


    private function adjustStatusCode($response): int
    {
        $statusCode = $response->status();

        if ($statusCode === 400 && !empty($response->body())) {
            try {
                $errorDetails = json_decode($response->body(), false, 512, JSON_THROW_ON_ERROR);

                if (is_object($errorDetails) && ($errorDetails->Message ?? null) === 'AUTHENTICATION_FAILED') {
                    return 401;
                }
            } catch (JsonException) {
                // Ignore parsing error, keep original status code
            }
        }

        return $statusCode;
    }

    public function buildPayload(array $payloadItems, int $psoApiVersion = 1, bool $useWrapper = false): array
    {
        if ($psoApiVersion === 1) {
            $data = [
                '@xmlns' => 'http://360Scheduling.com/Schema/dsScheduleData.xsd',
            ];

            foreach ($payloadItems as $itemKey => $itemValues) {
                $data[$itemKey] = $itemValues;
            }

            if ($useWrapper) {
                return [
                    'payloadToPso' => ['dsScheduleData' => $data]
                ];
            }
            return [
                'dsScheduleData' => $data
            ];

        }

    }


    /**
     * Executes an action that requires PSO authentication
     *
     * @param Request $request The request containing environment data
     * @param callable $action The action to execute if authentication is successful
     * @return JsonResponse The response from the action or auth error
     */
    protected
    function executeAuthenticatedAction(Request $request, callable $action): JsonResponse
    {
        if ($request->input('environment.sendToPso') === false) {
            return $action($request);
        }

        try {
            $response = $this->getPSOToken($request->environment);
            Log::info('going the auth route');

            if ($response->status() === 200) {
                Log::info('response was a 200, continuing with the action');
                // Merge the token into the request so it's available to the action
                $token = $response->getData()->message?->SessionToken ?? null;
                Log::info('token is ' . $token);
                $request->merge([
                    'environment' => array_merge(
                        (array)$request->input('environment', []),
                        compact('token')
                    ),
                ]);

                return $action($request);
            }

            return $response;

        } catch (Exception $e) {
            Log::error($e->getMessage());
            return $this->error('something totally funky went wrong', 500);
        }
    }


}

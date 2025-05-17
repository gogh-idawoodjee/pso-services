<?php

namespace App\Traits\V2;

use App\Classes\V2\PSOAuthService;
use App\Classes\V2\SendOrSimulateBuilder;
use App\Enums\InputMode;
use App\Enums\PsoEndpointSegment;
use App\Classes\V2\EntityBuilders\InputReferenceBuilder as InputReferenceNew;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;
use JsonException;
use SensitiveParameter;

trait PSOAssistV2
{
    use ApiResponses;

    /**
     * Send data to PSO endpoint
     *
     * @param array $payload Data to send
     * @param array $environmentData Environment configuration
     * @param PsoEndpointSegment $segment PSO API endpoint
     * @return JsonResponse Response from PSO
     * @throws JsonException
     */
    public function sendToPso($payload, array $environmentData, #[SensitiveParameter] string $sessionToken, PsoEndpointSegment $segment): JsonResponse
    {
        try {
            $timeout = config('psott.defaults.timeout', 10);
            $baseUrl = data_get($environmentData, 'baseUrl');
            $url = "{$baseUrl}/IFSSchedulingRESTfulGateway/api/v1/scheduling/{$segment->value}";

            $response = Http::timeout($timeout)
                ->connectTimeout($timeout)
                ->withHeaders(['apiKey' => $sessionToken])
                ->post($url, $payload);

            return $this->handleDataResponse($response);
        } catch (ConnectionException) {
            return $this->connectionFailureResponse();
        }
    }


    /**
     * Creates standard connection failure response
     *
     * @return JsonResponse
     */
    private function connectionFailureResponse(): JsonResponse
    {
        return response()->json(
            ['error' => 'Connection failed. The request timed out or the server could not be reached.'],
            504
        );
    }

    /**
     * Handle error responses and format them consistently.
     *
     * @param Response $response
     * @return JsonResponse
     */
    private function handleErrorResponse(Response $response): JsonResponse
    {
        $statusCode = $this->adjustStatusCode($response);
        $errorDetails = $this->parseResponseBody($response->body());

        return response()->json([
            'error' => $this->getErrorMessage($statusCode),
            'details' => $errorDetails,
        ], $statusCode);
    }

    /**
     * Get appropriate error message for status code
     *
     * @param int $statusCode HTTP status code
     * @return string Error message
     */
    private function getErrorMessage(int $statusCode): string
    {
        return match ($statusCode) {
            401 => 'Unauthorized. Please check your session or login credentials.',
            400 => 'Client Error. See Details.',
            404 => 'URL not found. Check the endpoint.',
            500 => 'Internal server error. Try again later.',
            default => 'Unexpected error.',
        };
    }

    /**
     * Parse response body safely
     *
     * @param string|null $body Response body
     * @return mixed Parsed JSON or original string
     */
    private function parseResponseBody(string|null $body): mixed
    {
        if (empty($body)) {
            return null;
        }

        try {
            return json_decode($body, false, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return $body;
        }
    }

    /**
     * Handle data responses.
     *
     * @param Response $response
     * @return JsonResponse
     * @throws JsonException
     */
    private function handleDataResponse(Response $response): JsonResponse
    {
        if ($response->successful()) {
            $psoResponse = $response->json();
            Log::info('pso response is ' . json_encode($psoResponse, JSON_THROW_ON_ERROR));
            return response()->json($psoResponse);
        }

        return $this->handleErrorResponse($response);
    }

    /**
     * Adjust the status code based on response content
     *
     * @param Response $response
     * @return int Appropriate status code
     */
    private function adjustStatusCode(Response $response): int
    {
        $statusCode = $response->status();

        if ($statusCode === 400 && !empty($response->body())) {
            try {
                $errorDetails = json_decode($response->body(), false, 512, JSON_THROW_ON_ERROR);

                if (is_object($errorDetails) && data_get($errorDetails, 'Message') === 'AUTHENTICATION_FAILED') {
                    return 401;
                }
            } catch (JsonException) {
                // Ignore parsing error, keep original status code
            }
        }

        return $statusCode;
    }

    /**
     * Build payload for PSO API
     *
     * @param array $payloadItems Items to include in payload
     * @param int $psoApiVersion API version
     * @param bool $useWrapper Whether to wrap the payload
     * @return array Formatted payload
     */
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

        // Consider adding support for other API versions or throw an exception
        return $payloadItems;
    }

    /**
     * Executes an action that requires PSO authentication
     *
     * @param Request $request The request containing environment data
     * @param callable $action The action to execute if authentication is successful
     * @return JsonResponse The response from the action or auth error
     */
    protected function executeAuthenticatedAction(Request $request, callable $action): JsonResponse
    {
        if (!data_get($request, 'environment.sendToPso')) {
            // continue if no auth is required
            return $action($request);
        }

        $authService = app(PSOAuthService::class);

        // Check if we already have a token
        if (data_get($request, 'environment.token')) {
            // Validate token if needed
            // $isValid = $authService->validateToken(data_get($request, 'environment.token'), data_get($request, 'environment', []));
            return $action($request);
        }


        try {
            $response = $authService->getToken(data_get($request, 'environment', []));
            Log::info('going the auth route');

            if ($response->status() === 200) {
                Log::info('response was a 200, continuing with the action');

                // Extract token from response
                $token = data_get($response->getData(), 'message.SessionToken');
                Log::info('token is ' . $token);

                // Merge the token into the request
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

    /**
     * @throws JsonException
     */
    public function sendOrSimulate(
        array       $payload,
        array       $environmentData,
        string|null $sessionToken,
        bool|null   $requiresRotaUpdate = null,
        string|null $rotaUpdateDescription = null,
        string|null $notSentArrayKey = null,
        string|null $additionalDetails = null,
        bool|null   $addInputReference = null
    ): JsonResponse
    {
        if ($addInputReference) {
//            $payload['Input_Reference'] = InputReferenceBuilder::make(
//                data_get($environmentData, 'datasetId'),
//                InputMode::CHANGE
//            );

            $payload['inputReference'] =
                InputReferenceNew::make(data_get($environmentData, 'datasetId'))
                    ->inputType(InputMode::CHANGE)
                    ->build();
        }

        if ($sessionToken) {
            $psoPayload = $this->buildPayload($payload);

            $psoResponse = $this->sendToPso(
                $psoPayload,
                $environmentData,
                $sessionToken,
                PsoEndpointSegment::DATA
            );
            // send rota update
            if ($requiresRotaUpdate) {
                $rotaUpdatePayload =
                    InputReferenceNew::make(data_get($environmentData, 'datasetId'))
                        ->inputType(InputMode::CHANGE)
                        ->description($rotaUpdateDescription) // todo test where this description comes from and if its required if requiresrotaupdate is provided
                        ->build();
                $this->sendToPso($rotaUpdatePayload, $environmentData, $sessionToken, PsoEndpointSegment::DATA);
            }


            if ($psoResponse->status() < 400) {
                return $this->ok($psoResponse->getData());
            }
            return $psoResponse;
        }

        $payloadArray = $notSentArrayKey ? [$notSentArrayKey => $payload] : $payload;
        return $this->notSentToPso($this->buildPayload($payloadArray, 1, true), $additionalDetails);
    }


    public function sendOrSimulateBuilder(): SendOrSimulateBuilder
    {
        return new SendOrSimulateBuilder($this);
    }

}

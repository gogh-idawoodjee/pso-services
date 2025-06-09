<?php

namespace App\Traits\V2;

use App\Classes\V2\PSOAuthService;
use App\Classes\V2\SendOrSimulateBuilder;
use App\Constants\PSOConstants;
use App\Enums\InputMode;
use App\Enums\PsoEndpointSegment;
use App\Classes\V2\EntityBuilders\InputReferenceBuilder as InputReferenceNew;
use App\Helpers\PSOHelper;
use App\Helpers\Stubs\SourceData;
use App\Helpers\Stubs\SourceDataParameter;
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

            return $this->error('Connection failed. The request timed out or the server could not be reached', 504);

        }
    }


    /**
     */

    public function getPsoData(
        string                       $datasetId,
        string                       $baseUrl,
        #[SensitiveParameter] string $sessionToken,
        PsoEndpointSegment           $segment,
        string|null                  $resourceId = null,
        bool                         $includeInput = false,
        bool                         $includeOutput = false,
        string|null                  $minDate = null,
        string|null                  $maxDate = null
    ): JsonResponse
    {
        try {
            $timeout = config('psott.defaults.timeout', 10);

            $endpoint = '/IFSSchedulingRESTfulGateway/api/v1/scheduling/' . $segment->value;

            $queryParams = [
                'includeOutput' => 'true',
                'datasetId' => $datasetId,
            ];

            if ($includeInput) {
                $queryParams['includeInput'] = 'true'; // Fixed the typo
            }

            if ($includeOutput) {
                $queryParams['includeOutput'] = 'true'; // Fixed the typo
            }

            if ($resourceId) {
                $queryParams['resourceId'] = $resourceId;
            }

            if ($minDate) {
                $queryParams['minimumDateTime'] = PSOHelper::toUrlEncodedIso8601($minDate);
            }

            if ($maxDate) {
                $queryParams['maximumDateTime'] = PSOHelper::toUrlEncodedIso8601($maxDate);
            }

            $url = "{$baseUrl}{$endpoint}?" . http_build_query($queryParams);

            $response = Http::timeout($timeout)
                ->connectTimeout($timeout)
                ->withHeaders(['apiKey' => $sessionToken])
                ->get($url);

            return $this->handleDataResponse($response);
        } catch (ConnectionException) {

            return $this->error('Connection failed. The request timed out or the server could not be reached', 504);

        }
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

        return $this->error([
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
     */
    private function handleDataResponse(Response $response): JsonResponse
    {
        if ($response->successful()) {
            $psoResponse = $response->json();
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
     *
     */
    protected function executeAuthenticatedAction(Request $request, callable $action): JsonResponse
    {
        // Get normalized auth details from either request body or headers
        $authDetails = $this->getAuthDetails($request);

        // Check if auth is required (sendToPso must be true)
        if (!data_get($authDetails, 'sendToPso')) {
            // No auth needed, just run the action
            return $action($request);
        }

        $authService = app(PSOAuthService::class);

        // If token already present, skip getting token and run action directly
        if (data_get($authDetails, 'token')) {
            return $action($request);
        }

        try {
            // Attempt to get a fresh token from the auth service
            $response = $authService->getToken($authDetails);

            if ($response->status() === 200) {
                // Extract token from response data
                $token = data_get($response->getData(), 'message.SessionToken');

                // Merge token into request's environment input for downstream consistency
                $request->merge([
                    'environment' => array_merge(
                        (array)$request->input('environment', []),
                        compact('token')
                    ),
                ]);

                // Run the action now that token is present
                return $action($request);
            }


            return $response;

        } catch (Exception $e) {
            Log::error('Auth error: ' . $e->getMessage());
            return $this->error('Authentication failed', 500);
        }
    }


    /**
     */
    public function sendOrSimulate(
        array       $payload,
        array       $environmentData,
        string|null $sessionToken,
        bool|null   $requiresRotaUpdate = null,
        string|null $rotaUpdateDescription = null,
//        string|null $notSentArrayKey = null,
        string|null $additionalDetails = null,
        bool|null   $addInputReference = null,
        string|null $inputReferenceDescription = null // ← new param
    ): JsonResponse
    {


        if ($addInputReference) {

            $payload['Input_Reference'] =
                InputReferenceNew::make(data_get($environmentData, 'datasetId'))
                    ->inputType(InputMode::CHANGE)
                    ->description($inputReferenceDescription)
                    ->build();
        }

        $psoPayload = $this->buildPayload($payload);
        $wrappedPayload = $this->buildPayload($payload, 1, true);
        if ($sessionToken) {


            $psoResponse = $this->sendToPso(
                $psoPayload,
                $environmentData,
                $sessionToken,
                PsoEndpointSegment::DATA
            );
            // send rota update
            // in sendOrSimulateBuilder()  ->requiresRotaUpdate() takes a true and description in  as params
            // the description becomes $rotaUpdateDescription
            if ($requiresRotaUpdate) {
                $rotaUpdatePayload['Input_Reference'] =
                    InputReferenceNew::make(data_get($environmentData, 'datasetId'))
                        ->inputType(InputMode::CHANGE)
                        ->description($rotaUpdateDescription) // see note above if statement
                        ->build();
                $rotaUpdatePayload['Source_Data'] = SourceData::make();
                $rotaUpdatePayload['Source_Data_Parameter'] = SourceDataParameter::make(
                    PSOConstants::SOURCE_DATA_PARAM_NAME,
                    PSOConstants::SOURCE_DATA_PARAM_VALUE
                );

                $this->sendToPso($this->buildPayload($rotaUpdatePayload), $environmentData, $sessionToken, PsoEndpointSegment::DATA);
            }

            if ($psoResponse->status() < 400) {
                return $this->sentToPso(['payloadToPso' => $wrappedPayload['payloadToPso'], 'responseFromPso' => $psoResponse->getData()], $additionalDetails);
            }
            return $psoResponse;
        }


        return $this->notSentToPso($wrappedPayload, $additionalDetails);
    }


    public function sendOrSimulateBuilder(): SendOrSimulateBuilder
    {
        return new SendOrSimulateBuilder($this);
    }

    protected function getAuthDetails(Request $request): array
    {
        // Try to get environment data from request input (POST/PATCH)
        $env = data_get($request->all(), 'environment', []);

        // Normalize headers keys to lowercase with first value
        $headers = collect($request->headers->all())->mapWithKeys(static fn($values, $key) => [
            strtolower($key) => $values[0] ?? null,
        ]);

        // Use data_get on env array, fallback to headers if missing
        $token = data_get($env, 'token', $headers->get('token'));
        $baseUrl = data_get($env, 'baseUrl', $headers->get('baseurl'));
        $accountId = data_get($env, 'accountId', $headers->get('accountid'));
        $username = data_get($env, 'username', $headers->get('username'));
        $password = data_get($env, 'password', $headers->get('password'));

        // Determine sendToPso
        $sendToPso = data_get($env, 'sendToPso');

        // If sendToPso not explicitly set in env, but env is empty (means headers used), force true
        if ($sendToPso === null && empty($env)) {
            $sendToPso = true;
        }

        return compact('token', 'baseUrl', 'accountId', 'username', 'password', 'sendToPso');
    }


}

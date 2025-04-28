<?php

namespace App\Classes\V2;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use JsonException;
use App\Traits\V2\ApiResponses;

class PSOAuthService
{
    use ApiResponses;

    /**
     * Get authentication token from PSO
     *
     * @param array $environment Authentication environment data
     * @return JsonResponse Response containing token or error message
     */
    public function getToken(array $environment): JsonResponse
    {
        // Return existing token if available
        if (data_get($environment, 'token')) {
            return $this->ok('valid token', data_get($environment, 'token'));
        }

        try {
            $timeout = config('psott.defaults.timeout', 10);
            $url = data_get($environment, 'baseUrl') . "/IFSSchedulingRESTfulGateway/api/v1/scheduling/session";

            $response = Http::asForm()
                ->timeout($timeout)
                ->connectTimeout($timeout)
                ->post($url, [
                    'accountId' => data_get($environment, 'accountId'),
                    'userName' => data_get($environment, 'username'),
                    'password' => data_get($environment, 'password'),
                ]);

            return $this->handleSessionResponse($response);
        } catch (ConnectionException) {
            return $this->connectionFailureResponse();
        }
    }

    /**
     * Validate if a token is still valid
     *
     * @param string $token The token to validate
     * @param array $environment Environment configuration
     * @return bool Whether the token is valid
     */
    public function validateToken(#[\SensitiveParameter] string $token, array $environment): bool
    {
        // Implement token validation logic here
        // This is a placeholder - you would need to implement actual validation
        // based on your PSO API specifications

        return true; // Assuming token is valid for now
    }

    /**
     * Handle session responses.
     *
     * @param Response $response
     * @return JsonResponse
     */
    private function handleSessionResponse(Response $response): JsonResponse
    {
        if ($response->successful()) {
            $json = $response->json();
            $message = data_get($json, 'SessionToken')
                ? ['SessionToken' => data_get($json, 'SessionToken')]
                : $json;

            return response()->json([
                'data' => 'Auth was successful.',
                'message' => $message,
                'status' => $response->status(),
            ]);
        }

        return $this->handleErrorResponse($response);
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
    private function parseResponseBody(string|null $body)
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
}

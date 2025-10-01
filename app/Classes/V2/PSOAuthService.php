<?php

namespace App\Classes\V2;

use App\Enums\PsoEndpointSegment;
use App\Helpers\UrlHelper;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use JsonException;
use App\Traits\V2\ApiResponses;
use SensitiveParameter;
use Throwable;

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

        $totalTimeout = (int)config('psott.defaults.timeout', 10);
        $connectTimeout = min(3, max(1, $totalTimeout - 1)); // 1–3s connect budget

        try {
            $base = UrlHelper::normalizeBaseUrl(data_get($environment, 'baseUrl'));
            $url = $base . "/IFSSchedulingRESTfulGateway/api/v1/scheduling/" . PsoEndpointSegment::SESSION->value;

            // (optional) lightweight debug log without leaking secrets
            Log::info('PSO auth request', [
                'url' => $url,
                'accountId' => data_get($environment, 'accountId'),
                'userName' => data_get($environment, 'username'),
            ]);

            $response = Http::asForm()
                ->timeout($totalTimeout)
                ->connectTimeout($connectTimeout)
                ->post($url, [
                    'accountId' => data_get($environment, 'accountId'),
                    'userName' => data_get($environment, 'username'),
                    'password' => data_get($environment, 'password'),
                ]);

            return $this->handleSessionResponse($response);
        } catch (ConnectionException $e) {
            // Network / DNS / TLS / connect/read timeout → return YOUR 504 JSON
            return response()->json([
                'error' => 'Connection failed. The request timed out or the server could not be reached.',
                'details' => $e->getMessage(),
            ], 504);
        } catch (Throwable $e) {
            // Malformed URL, invalid args, JSON issues, etc. → clean 422 JSON
            return response()->json([
                'error' => 'Request could not be dispatched',
                'details' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Validate if a token is still valid
     * (placeholder – implement per PSO spec)
     */
    public function validateToken(#[SensitiveParameter] string $token, array $environment): bool
    {
        return true;
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

            // Some PSO gateways nest the token; normalize so caller gets the same shape
            $message = data_get($json, 'SessionToken')
                ? ['SessionToken' => data_get($json, 'SessionToken')]
                : $json;

            return response()->json([
                'data' => 'Auth was successful.',
                'message' => $message,
                'status' => $response->status(),
            ]);
        }

        // For non-2xx, preserve your existing mapping logic
        return $this->handleErrorResponse($response);
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
     * Adjust the status code based on response content
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
                // ignore parse errors
            }
        }

        return $statusCode;
    }

}

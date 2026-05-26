<?php

namespace App\Traits\V2;

use Illuminate\Http\JsonResponse;

trait ApiResponses
{
    protected function ok(mixed $data = [], string|null $message = null): JsonResponse
    {
        return $this->success($data, $message);
    }

    protected function notSentToPso(mixed $data = [], string|null $additionalDetails = null): JsonResponse
    {
        return $this->success($data, message: 'Successful. Not sent to PSO by Request', additionalDetails: $additionalDetails, statusCode: 202);
    }

    protected function sentToPso(mixed $data = [], string|null $additionalDetails = null, string|null $resultsUrl = null): JsonResponse
    {
        return $this->success($data, message: 'Successful. Sent to PSO', additionalDetails: $additionalDetails, resultsUrl: $resultsUrl);
    }

    protected function success(mixed $data = [], string|null $message = null, string|null $additionalDetails = null, string|null $resultsUrl = null, int $statusCode = 200): JsonResponse
    {
        $response = [
            'data' => $data,
            'status' => $statusCode,
        ];

        if ($message !== null) {
            $response['message'] = $message;
        }

        if ($additionalDetails !== null) {
            $response['additionalDetails'] = $additionalDetails;
        }

        if ($resultsUrl !== null) {
            $response['resultsUrl'] = $resultsUrl;
        }

        return response()->json($response, $statusCode);
    }

    protected function error(mixed $message, int $statusCode): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'status' => $statusCode
        ], $statusCode);
    }
}

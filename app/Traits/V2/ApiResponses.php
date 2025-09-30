<?php

namespace App\Traits\V2;

use Illuminate\Http\JsonResponse;
use SensitiveParameter;

trait ApiResponses
{
    protected function ok($data = [], $message = null): JsonResponse
    {
        return $this->success($data, $message);
    }

    protected function notSentToPso($data = [], $additionalDetails = null): JsonResponse
    {
        return $this->success($data, 'Successful. Not sent to PSO by Request', $additionalDetails, null, 202);
        //                                                                                          ^^^^  ^^^
        //                                                                                          resultsUrl, statusCode
    }

    protected function sentToPso($data = [], $additionalDetails = null, $resultsUrl = null): JsonResponse
    {
        return $this->success($data, 'Successful. Sent to PSO', $additionalDetails, $resultsUrl);
    }


    protected function success($data = [], $message = null, $additionalDetails = null, $resultsUrl = null, $statusCode = 200): JsonResponse
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

    protected function error($message, #[SensitiveParameter] $statusCode): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'status' => $statusCode
        ], $statusCode);
    }
}

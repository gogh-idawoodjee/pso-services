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

    protected function success($data = [], $message = null, $statusCode = 200): JsonResponse
    {
        return response()->json([
            'data' => $data,
            'message' => $message,
            'status' => $statusCode
        ], $statusCode);
    }

    protected function error($message, #[SensitiveParameter] $statusCode): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'status' => $statusCode
        ], $statusCode);
    }
}

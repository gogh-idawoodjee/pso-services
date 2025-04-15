<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponses
{

    protected function ok($message): JsonResponse
    {
        return $this->success($message);
    }

    protected function success($message, $statusCode = 200): JsonResponse
    {

        return response()->json([
            'message' => $message,
            'status' => $statusCode,
        ], $statusCode);
    }

}

<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V2\HealthCheckRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class HealthCheckController extends Controller
{
    public function check(HealthCheckRequest $request): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'message' => 'The server is running',
            'timestamp' => now(),
        ], Response::HTTP_OK);
    }
}

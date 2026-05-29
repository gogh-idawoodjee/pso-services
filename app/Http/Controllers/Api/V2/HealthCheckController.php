<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V2\HealthCheckRequest;
use App\Traits\V2\PSOAssistV2;
use Illuminate\Http\JsonResponse;

/**
 * @group System
 */
class HealthCheckController extends Controller
{
    use PSOAssistV2;

    /**
     * Health Check.
     *
     * Validates that authentication credentials are valid by attempting to obtain a PSO token.
     *
     * @response 200 scenario="Healthy" {"data": {"status": "healthy"}, "status": 200}
     * @response 401 scenario="Unauthorized" {"message": "Unauthorized. Please check your session or login credentials.", "status": 401}
     */
    public function check(HealthCheckRequest $request): JsonResponse
    {
        return $this->executeAuthenticatedAction($request, function () {
            return $this->ok(['status' => 'healthy']);
        });
    }
}

<?php

namespace App\Http\Controllers\Api\V2;

use App\DataTransferObjects\PsoContext;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V2\LoadTestRequest;
use App\Services\V2\LoadTestService;
use App\Traits\V2\PSOAssistV2;
use Illuminate\Http\JsonResponse;

/**
 * @group System
 */
class SandboxController extends Controller
{
    use PSOAssistV2;

    /**
     * Run Load Test
     *
     * Stub for the V2 load-test feature. The V1 version has been removed;
     * this endpoint is a placeholder until the feature is redesigned
     * against V2's request shape. See issue #36.
     *
     * @response 501 scenario="Not implemented" {"message": "V2 load test is not yet implemented", "status": 501}
     */
    public function runLoadTest(LoadTestRequest $request, LoadTestService $loadTestService): JsonResponse
    {
        return $this->executeAuthenticatedAction($request, fn(LoadTestRequest $req) =>
            $loadTestService->run(PsoContext::fromRequest($req))
        );
    }
}

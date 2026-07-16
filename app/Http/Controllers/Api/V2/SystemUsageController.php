<?php

namespace App\Http\Controllers\Api\V2;

use App\DataTransferObjects\PsoContext;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V2\SystemUsageRequest;
use App\Services\V2\AssistService;
use App\Traits\V2\PSOAssistV2;
use Illuminate\Http\JsonResponse;

/**
 * @group System
 */
class SystemUsageController extends Controller
{
    use PSOAssistV2;

    /**
     * Get System Usage
     *
     * @response 200 scenario="Success" {"data": {}, "status": 200}
     */
    public function show(SystemUsageRequest $request, AssistService $assistService): JsonResponse
    {
        return $this->executeAuthenticatedAction($request, fn(SystemUsageRequest $req) =>
            $assistService->getSystemUsage(PsoContext::fromRequest($req))
        );
    }
}

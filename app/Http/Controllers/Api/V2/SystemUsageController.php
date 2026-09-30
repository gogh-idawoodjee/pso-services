<?php

namespace App\Http\Controllers\Api\V2;

use App\DataTransferObjects\PsoContext;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V2\SystemUsageRequest;
use App\Services\V2\AssistService;
use App\Traits\V2\PSOAssistV2;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\Response;
use Illuminate\Http\JsonResponse;

#[Group('System')]
class SystemUsageController extends Controller
{
    use PSOAssistV2;

    /**
     * Get System Usage
     */
    #[Response(200, 'Raw usage data from PSO, passed through without the usual envelope', type: 'object')]
    public function show(SystemUsageRequest $request, AssistService $assistService): JsonResponse
    {
        return $this->executeAuthenticatedAction($request, fn(SystemUsageRequest $req) =>
            $assistService->getSystemUsage(
                PsoContext::fromRequest($req),
                $req->input('minDate'),
                $req->input('maxDate'),
            )
        );
    }
}

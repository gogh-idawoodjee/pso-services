<?php

namespace App\Http\Controllers\Api\V2;

use App\DataTransferObjects\PsoContext;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V2\ResourceEventRequest;
use App\Services\V2\ResourceService;
use App\Traits\V2\PSOAssistV2;
use Illuminate\Http\JsonResponse;

class ResourceEventController extends Controller
{
    use PSOAssistV2;

    /**
     * Create a new resource event.
     */
    public function store(ResourceEventRequest $request, ResourceService $resourceService): JsonResponse
    {
        return $this->executeAuthenticatedAction($request, fn(ResourceEventRequest $req) =>
            $resourceService->createEvent(PsoContext::fromRequest($req))
        );
    }
}

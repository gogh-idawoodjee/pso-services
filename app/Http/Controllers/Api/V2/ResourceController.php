<?php

namespace App\Http\Controllers\Api\V2;

use App\DataTransferObjects\PsoContext;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V2\ResourceRequest;
use App\Services\V2\ResourceService;
use App\Traits\V2\PSOAssistV2;
use Illuminate\Http\JsonResponse;

class ResourceController extends Controller
{
    use PSOAssistV2;

    /**
     * Display the specified resource.
     */
    public function show(ResourceRequest $request, string $resourceId, ResourceService $resourceService): JsonResponse
    {
        return $this->executeAuthenticatedAction($request, fn(ResourceRequest $req) =>
            $resourceService->getResource(PsoContext::fromRequest($req), $resourceId)
        );
    }

    /**
     * Get All Resources in Dataset.
     */
    public function index(ResourceRequest $request, ResourceService $resourceService): JsonResponse
    {
        return $this->executeAuthenticatedAction($request, fn(ResourceRequest $req) =>
            $this->ok(['resources' => $resourceService->getResourceSelectOptions(PsoContext::fromRequest($req))])
        );
    }
}

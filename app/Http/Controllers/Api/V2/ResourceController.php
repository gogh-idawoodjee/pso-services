<?php

namespace App\Http\Controllers\Api\V2;

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
    public function show(ResourceRequest $request, string $resourceId): JsonResponse
    {
        return $this->executeAuthenticatedAction($request, function (ResourceRequest $req) use ($resourceId) {
            $resourceService = new ResourceService(
                $req->input('environment.token'),
                $req->validated(),
            );

            $datasetId = $req->headers->get('datasetId');
            $baseUrl = $req->headers->get('baseUrl');

            return $resourceService->getResource($datasetId, $resourceId, $baseUrl);
        });
    }

    /**
     * Get All Resources in Dataset.
     */
    public function index(ResourceRequest $request): JsonResponse
    {
        return $this->executeAuthenticatedAction($request, function (ResourceRequest $req) {
            $resourceService = new ResourceService(
                $req->input('environment.token'),
                $req->validated(),
            );

            $datasetId = $req->headers->get('datasetId');
            $baseUrl = $req->headers->get('baseUrl');

            return $this->ok(['resources' => $resourceService->getResourceList($datasetId, $baseUrl)->toSelectOptions()->getSelectOptions()]);
        });
    }
}

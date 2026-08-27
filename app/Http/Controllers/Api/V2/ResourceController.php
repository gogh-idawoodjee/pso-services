<?php

namespace App\Http\Controllers\Api\V2;

use App\DataTransferObjects\PsoContext;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V2\ResourceRequest;
use App\Http\Requests\Api\V2\ResourceStoreRequest;
use App\Services\V2\ResourceService;
use App\Traits\V2\PSOAssistV2;
use Illuminate\Http\JsonResponse;

/**
 * @group Resources
 */
class ResourceController extends Controller
{
    use PSOAssistV2;

    /**
     * Create Resources
     *
     * Creates one or more resources (RAM_Resource) in the ARP modelling data,
     * along with their starting location, skills, and region assignments.
     *
     * @response 200 scenario="Sent to PSO" {"data": {"payloadToPso": {"DsModelling": {"@xmlns": "http://360Scheduling.com/Schema/DsModelling.xsd", "RAM_Update": {"dataset_id": "dataset_123"}, "RAM_Resource": [{"id": "RES-001"}], "RAM_Location": [{"id": "RES-001"}]}}, "responseFromPso": {}}, "status": 200, "message": "Successful. Sent to PSO"}
     * @response 202 scenario="Dry run" {"data": {"payloadToPso": {"DsModelling": {"@xmlns": "http://360Scheduling.com/Schema/DsModelling.xsd", "RAM_Update": {"dataset_id": "dataset_123"}, "RAM_Resource": [{"id": "RES-001"}], "RAM_Location": [{"id": "RES-001"}]}}}, "status": 202, "message": "Successful. Not sent to PSO by Request"}
     */
    public function store(ResourceStoreRequest $request, ResourceService $resourceService): JsonResponse
    {
        return $this->executeAuthenticatedAction($request, fn (ResourceStoreRequest $req) => $resourceService->createResource(PsoContext::fromRequest($req))
        );
    }

    /**
     * Display the specified resource.
     *
     * @response 200 scenario="Success" {"data": {}, "status": 200}
     */
    public function show(ResourceRequest $request, string $resourceId, ResourceService $resourceService): JsonResponse
    {
        return $this->executeAuthenticatedAction($request, fn (ResourceRequest $req) => $resourceService->getResource(PsoContext::fromRequest($req), $resourceId)
        );
    }

    /**
     * Get All Resources in Dataset.
     *
     * @response 200 scenario="Success" {"data": {"resources": [{"id": "RES-001", "name": "John Smith"}, {"id": "RES-002", "name": "Jane Doe"}]}, "status": 200}
     */
    public function index(ResourceRequest $request, ResourceService $resourceService): JsonResponse
    {
        return $this->executeAuthenticatedAction($request, fn (ResourceRequest $req) => $this->ok(['resources' => $resourceService->getResourceSelectOptions(PsoContext::fromRequest($req))])
        );
    }
}

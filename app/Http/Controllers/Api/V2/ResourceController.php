<?php

namespace App\Http\Controllers\Api\V2;

use App\DataTransferObjects\PsoContext;
use App\Http\Controllers\Controller;
use App\Http\OpenApi\ErrorResponse;
use App\Http\OpenApi\NotSentToPso;
use App\Http\OpenApi\OkResponse;
use App\Http\OpenApi\SentToPso;
use App\Http\Requests\Api\V2\ResourceRequest;
use App\Http\Requests\Api\V2\ResourceStoreRequest;
use App\Services\V2\ResourceService;
use App\Traits\V2\PSOAssistV2;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;

#[Group('Resources')]
class ResourceController extends Controller
{
    use PSOAssistV2;

    /**
     * Create Resources
     *
     * Creates one or more resources (RAM_Resource) in the ARP modelling data,
     * along with their starting location, skills, and region assignments.
     */
    #[SentToPso(examples: [[
                    'payloadToPso' => [
                        'DsModelling' => [
                            '@xmlns' => 'http://360Scheduling.com/Schema/DsModelling.xsd',
                            'RAM_Update' => ['dataset_id' => 'dataset_123'],
                            'RAM_Resource' => [['id' => 'RES-001']],
                            'RAM_Location' => [['id' => 'RES-001']],
                        ],
                    ],
                    'responseFromPso' => new \stdClass,
                ]])]
    #[NotSentToPso(examples: [[
                    'payloadToPso' => [
                        'DsModelling' => [
                            '@xmlns' => 'http://360Scheduling.com/Schema/DsModelling.xsd',
                            'RAM_Update' => ['dataset_id' => 'dataset_123'],
                            'RAM_Resource' => [['id' => 'RES-001']],
                            'RAM_Location' => [['id' => 'RES-001']],
                        ],
                    ],
                ]])]
    public function store(ResourceStoreRequest $request, ResourceService $resourceService): JsonResponse
    {
        return $this->executeAuthenticatedAction($request, fn (ResourceStoreRequest $req) => $resourceService->createResource(PsoContext::fromRequest($req))
        );
    }

    /**
     * Display the specified resource.
     */
    #[OkResponse(data: 'array{resource: array{personal: array{full_name: string, first_name: string|null, surname: string|null}, additional_attributes: mixed, resource_id: string, resource_type: array{type_id: string|null, description: string|null}, note: string|null, max_travel: array{value: string|null, source: string|null, formatted: string|null}, max_travel_outside_shift_to_first_activity: array{value: string|null, source: string|null, formatted: string|null}, max_travel_outside_shift_to_home: array{value: string|null, source: string|null, formatted: string|null}, location: array<string, mixed>, regions: array{items: list<mixed>, total: int}, skills: array{items: list<mixed>, total: int}, shifts: mixed}}')]
    #[ErrorResponse(404, 'Resource not found')]
    public function show(ResourceRequest $request, string $resourceId, ResourceService $resourceService): JsonResponse
    {
        return $this->executeAuthenticatedAction($request, fn (ResourceRequest $req) => $resourceService->getResource(PsoContext::fromRequest($req), $resourceId)
        );
    }

    /**
     * Get All Resources in Dataset.
     */
    #[OkResponse(data: 'array{resources: array<string, string>}', examples: [['data' => ['resources' => ['RES-001' => 'John Smith', 'RES-002' => 'Jane Doe']]]])]
    public function index(ResourceRequest $request, ResourceService $resourceService): JsonResponse
    {
        return $this->executeAuthenticatedAction($request, fn (ResourceRequest $req) => $this->ok(['resources' => $resourceService->getResourceSelectOptions(PsoContext::fromRequest($req))])
        );
    }
}

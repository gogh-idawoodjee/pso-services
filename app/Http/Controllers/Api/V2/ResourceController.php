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
    #[OkResponse(data: 'array{resource: array{personal: array{full_name: string, first_name: string|null, surname: string|null}, additional_attributes: mixed, resource_id: string, resource_type: array{type_id: string|null, description: string|null}, note: string|null, max_travel: array{value: string|null, source: string|null, formatted: string|null}, max_travel_outside_shift_to_first_activity: array{value: string|null, source: string|null, formatted: string|null}, max_travel_outside_shift_to_home: array{value: string|null, source: string|null, formatted: string|null}, location: array<string, mixed>, regions: array{items: list<mixed>, total: int}, skills: array{items: list<mixed>, total: int}, shifts: mixed}}', examples: [[
        'data' => [
            'resource' => [
                'personal' => ['full_name' => 'Jane Smith', 'first_name' => 'Jane', 'surname' => 'Smith'],
                'additional_attributes' => null,
                'resource_id' => 'RES-001',
                'resource_type' => ['type_id' => 'TECH', 'description' => 'Technician'],
                'note' => null,
                'max_travel' => ['value' => 'PT1H', 'source' => 'resource', 'formatted' => '1 hour'],
                'max_travel_outside_shift_to_first_activity' => ['value' => 'PT30M', 'source' => 'resource', 'formatted' => '30 minutes'],
                'max_travel_outside_shift_to_home' => ['value' => 'PT30M', 'source' => 'resource', 'formatted' => '30 minutes'],
                'location' => ['same_start_and_end' => true, 'google_reverse_geocode_lookup' => ['start' => '100 King St W, Toronto, ON M5X 1A9, Canada', 'end' => '100 King St W, Toronto, ON M5X 1A9, Canada'], 'pso' => ['start' => ['id' => 'LOC-001', 'name' => 'Home', 'latitude' => 43.6532, 'longitude' => -79.3832, 'address_line1' => '100 King St W', 'city' => 'Toronto', 'province' => 'ON', 'postal_code' => 'M5X 1A9'], 'end' => ['id' => 'LOC-001', 'name' => 'Home', 'latitude' => 43.6532, 'longitude' => -79.3832, 'address_line1' => '100 King St W', 'city' => 'Toronto', 'province' => 'ON', 'postal_code' => 'M5X 1A9']]],
                'regions' => ['items' => [['id' => 'NORTH', 'description' => 'North Region']], 'total' => 1],
                'skills' => ['items' => [['id' => 'ELECTRICAL', 'description' => 'Electrical']], 'total' => 1],
                'shifts' => [],
            ],
        ],
    ]])]
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

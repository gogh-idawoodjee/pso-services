<?php

namespace App\Http\Controllers\Api\V2;

use App\DataTransferObjects\PsoContext;
use App\Http\Controllers\Controller;
use App\Http\OpenApi\NotSentToPso;
use App\Http\OpenApi\SentToPso;
use App\Http\Requests\Api\V2\UnavailabilityRequest;
use App\Http\Requests\Api\V2\UnavailabilityUpdateRequest;
use App\Services\V2\ResourceService;
use App\Traits\V2\PSOAssistV2;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;

#[Group('Resources')]
class ResourceUnavailabilityController extends Controller
{
    use PSOAssistV2;

    /**
     * Create a new resource unavailability.
     */
    #[SentToPso(examples: [['payloadToPso' => ['DsModelling' => ['@xmlns' => 'http://360Scheduling.com/Schema/DsModelling.xsd', 'RAM_Update' => ['dataset_id' => 'dataset_123'], 'RAM_Unavailability' => ['id' => 'UNAV-001', 'resource_id' => 'RES-001']]], 'responseFromPso' => new \stdClass]])]
    #[NotSentToPso(examples: [['payloadToPso' => ['DsModelling' => ['@xmlns' => 'http://360Scheduling.com/Schema/DsModelling.xsd', 'RAM_Update' => ['dataset_id' => 'dataset_123'], 'RAM_Unavailability' => ['id' => 'UNAV-001', 'resource_id' => 'RES-001']]]]])]
    public function store(UnavailabilityRequest $request, ResourceService $resourceService): JsonResponse
    {
        return $this->executeAuthenticatedAction($request, fn(UnavailabilityRequest $req) =>
            $resourceService->createUnavailability(PsoContext::fromRequest($req))
        );
    }

    /**
     * Update one or more existing unavailabilities.
     */
    #[SentToPso(examples: [['payloadToPso' => ['DsModelling' => ['@xmlns' => 'http://360Scheduling.com/Schema/DsModelling.xsd', 'RAM_Update' => ['dataset_id' => 'dataset_123'], 'RAM_Unavailability' => [['id' => 'UNAV-001'], ['id' => 'UNAV-002']]]], 'responseFromPso' => new \stdClass]])]
    #[NotSentToPso(examples: [['payloadToPso' => ['DsModelling' => ['@xmlns' => 'http://360Scheduling.com/Schema/DsModelling.xsd', 'RAM_Update' => ['dataset_id' => 'dataset_123'], 'RAM_Unavailability' => [['id' => 'UNAV-001'], ['id' => 'UNAV-002']]]]]])]
    public function update(UnavailabilityUpdateRequest $request, ResourceService $resourceService): JsonResponse
    {
        return $this->executeAuthenticatedAction($request, fn(UnavailabilityUpdateRequest $req) =>
            $resourceService->updateUnavailability(PsoContext::fromRequest($req))
        );
    }
}

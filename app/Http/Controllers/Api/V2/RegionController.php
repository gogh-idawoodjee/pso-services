<?php

namespace App\Http\Controllers\Api\V2;

use App\DataTransferObjects\PsoContext;
use App\Http\Controllers\Controller;
use App\Http\OpenApi\NotSentToPso;
use App\Http\OpenApi\SentToPso;
use App\Http\Requests\Api\V2\RegionRequest;
use App\Services\V2\RegionService;
use App\Traits\V2\PSOAssistV2;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;

#[Group('System')]
class RegionController extends Controller
{
    use PSOAssistV2;

    /**
     * Create Regions (RAM_Division)
     *
     * Creates one or more regions/divisions in the ARP modelling data. Unlike
     * scheduling payloads, this uses the DsModelling schema, not dsScheduleData.
     */
    #[SentToPso(examples: [[
                    'payloadToPso' => [
                        'DsModelling' => [
                            '@xmlns' => 'http://360Scheduling.com/Schema/DsModelling.xsd',
                            'RAM_Update' => ['dataset_id' => 'dataset_123'],
                            'RAM_Division' => [['id' => 'NORTH', 'send' => true]],
                        ],
                    ],
                    'responseFromPso' => new \stdClass,
                ]])]
    #[NotSentToPso(examples: [[
                    'payloadToPso' => [
                        'DsModelling' => [
                            '@xmlns' => 'http://360Scheduling.com/Schema/DsModelling.xsd',
                            'RAM_Update' => ['dataset_id' => 'dataset_123'],
                            'RAM_Division' => [['id' => 'NORTH', 'send' => true]],
                        ],
                    ],
                ]])]
    public function store(RegionRequest $request, RegionService $regionService): JsonResponse
    {
        return $this->executeAuthenticatedAction($request, fn (RegionRequest $req) => $regionService->createDivisions(PsoContext::fromRequest($req))
        );
    }
}

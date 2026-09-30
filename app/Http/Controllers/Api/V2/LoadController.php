<?php

namespace App\Http\Controllers\Api\V2;

use App\DataTransferObjects\PsoContext;
use App\Http\Controllers\Controller;
use App\Http\OpenApi\NotSentToPso;
use App\Http\OpenApi\SentToPso;
use App\Http\Requests\Api\V2\LoadPsoRequest;
use App\Http\Requests\Api\V2\UpdateRotaRequest;
use App\Services\V2\LoadService;
use App\Traits\V2\PSOAssistV2;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;

#[Group('System')]
class LoadController extends Controller
{
    use PSOAssistV2;

    /**
     * Initialize PSO
     */
    #[SentToPso(examples: [[
                    'payloadToPso' => [
                        'dsScheduleData' => [
                            '@xmlns' => 'http://360Scheduling.com/Schema/dsScheduleData.xsd',
                            'Input_Reference' => [
                                'id' => 'abc123',
                                'input_type' => 'LOAD',
                                'dataset_id' => 'dataset_123',
                                'organisation_id' => '2',
                            ],
                        ],
                    ],
                    'responseFromPso' => new \stdClass,
                ]])]
    #[NotSentToPso(examples: [[
                    'payloadToPso' => [
                        'dsScheduleData' => [
                            '@xmlns' => 'http://360Scheduling.com/Schema/dsScheduleData.xsd',
                            'Input_Reference' => [
                                'id' => 'abc123',
                                'input_type' => 'LOAD',
                                'dataset_id' => 'dataset_123',
                                'organisation_id' => '2',
                            ],
                        ],
                    ],
                ]])]
    public function store(LoadPsoRequest $request, LoadService $loadService): JsonResponse
    {
        return $this->executeAuthenticatedAction($request, fn(LoadPsoRequest $req) =>
            $loadService->loadPSO(PsoContext::fromRequest($req))
        );
    }

    /**
     * Send Rota to DSE
     */
    #[SentToPso]
    #[NotSentToPso]
    public function updateRota(UpdateRotaRequest $request, LoadService $loadService): JsonResponse
    {
        return $this->executeAuthenticatedAction($request, fn(UpdateRotaRequest $req) =>
            $loadService->updateRota(PsoContext::fromRequest($req))
        );
    }
}

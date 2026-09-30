<?php

namespace App\Http\Controllers\Api\V2;

use App\DataTransferObjects\PsoContext;
use App\Http\Controllers\Controller;
use App\Http\OpenApi\NotSentToPso;
use App\Http\OpenApi\SentToPso;
use App\Http\Requests\Api\V2\DeleteObjectRequest;
use App\Services\V2\DeleteService;
use App\Traits\V2\PSOAssistV2;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;

#[Group('System')]
class DeleteObjectController extends Controller
{
    use PSOAssistV2;

    /**
     * Generic Delete Service
     */
    #[SentToPso(examples: [[
                    'payloadToPso' => [
                        'dsScheduleData' => [
                            '@xmlns' => 'http://360Scheduling.com/Schema/dsScheduleData.xsd',
                            'Object_Deletion' => [
                                [
                                    'object_type_id' => 'Activity',
                                    'object_pk1' => 'ACT-001',
                                    'object_pk_name1' => 'id',
                                ],
                            ],
                        ],
                    ],
                    'responseFromPso' => new \stdClass,
                ]])]
    #[NotSentToPso(examples: [[
                    'payloadToPso' => [
                        'dsScheduleData' => [
                            '@xmlns' => 'http://360Scheduling.com/Schema/dsScheduleData.xsd',
                            'Object_Deletion' => [
                                [
                                    'object_type_id' => 'Activity',
                                    'object_pk1' => 'ACT-001',
                                    'object_pk_name1' => 'id',
                                ],
                            ],
                        ],
                    ],
                ]])]
    public function destroy(DeleteObjectRequest $request, DeleteService $deleteService): JsonResponse
    {
        return $this->executeAuthenticatedAction($request, fn(DeleteObjectRequest $req) =>
            $deleteService->deleteObject(PsoContext::fromRequest($req))
        );
    }
}

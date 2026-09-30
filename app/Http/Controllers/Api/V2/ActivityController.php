<?php

namespace App\Http\Controllers\Api\V2;

use App\DataTransferObjects\PsoContext;
use App\Http\Controllers\Controller;
use App\Http\OpenApi\NotSentToPso;
use App\Http\OpenApi\SentToPso;
use App\Http\Requests\Api\V2\ActivityDeleteRequest;
use App\Http\Requests\Api\V2\ActivityStoreRequest;
use App\Services\V2\ActivityService;
use App\Traits\V2\PSOAssistV2;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;

#[Group('Activities')]
class ActivityController extends Controller
{
    use PSOAssistV2;

    /**
     * Create Activity.
     *
     * Creates a new activity, always starting in the UNALLOCATED status.
     */
    #[SentToPso(examples: [[
                    'payloadToPso' => [
                        'dsScheduleData' => [
                            '@xmlns' => 'http://360Scheduling.com/Schema/dsScheduleData.xsd',
                            'Activity' => ['id' => 'act-123'],
                        ],
                    ],
                    'responseFromPso' => new \stdClass,
                ]])]
    #[NotSentToPso(examples: [[
                    'payloadToPso' => [
                        'dsScheduleData' => [
                            '@xmlns' => 'http://360Scheduling.com/Schema/dsScheduleData.xsd',
                            'Activity' => ['id' => 'act-123'],
                        ],
                    ],
                ]])]
    public function store(ActivityStoreRequest $request, ActivityService $activityService): JsonResponse
    {
        return $this->executeAuthenticatedAction($request, fn(ActivityStoreRequest $req) =>
            $activityService->store(PsoContext::fromRequest($req))
        );
    }

    /**
     * Delete Activity or Activities.
     *
     * Deletes one or more activities.
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
    public function destroy(ActivityDeleteRequest $request, ActivityService $activityService): JsonResponse
    {
        return $this->executeAuthenticatedAction($request, fn(ActivityDeleteRequest $req) =>
            $activityService->deleteActivities(PsoContext::fromRequest($req))
        );
    }
}

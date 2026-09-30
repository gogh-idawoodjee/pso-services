<?php

namespace App\Http\Controllers\Api\V2;

use App\DataTransferObjects\PsoContext;
use App\Http\Controllers\Controller;
use App\Http\OpenApi\NotSentToPso;
use App\Http\OpenApi\SentToPso;
use App\Http\Requests\Api\V2\ActivityStatusRequest;
use App\Services\V2\ActivityService;
use App\Traits\V2\PSOAssistV2;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;

#[Group('Activities')]
class ActivityStatusController extends Controller
{
    use PSOAssistV2;

    /**
     * Update the Task Status of an Activity.
     */
    #[SentToPso(examples: [[
                    'payloadToPso' => [
                        'dsScheduleData' => [
                            '@xmlns' => 'http://360Scheduling.com/Schema/dsScheduleData.xsd',
                            'Activity_Status' => [
                                'activity_id' => 'ACT-001',
                                'status_id' => 'allocated',
                                'resource_id' => 'RES-001',
                            ],
                        ],
                    ],
                    'responseFromPso' => new \stdClass,
                ]])]
    #[NotSentToPso(examples: [[
                    'payloadToPso' => [
                        'dsScheduleData' => [
                            '@xmlns' => 'http://360Scheduling.com/Schema/dsScheduleData.xsd',
                            'Activity_Status' => ['activity_id' => 'ACT-001', 'status_id' => 'allocated'],
                        ],
                    ],
                ]])]
    public function update(ActivityStatusRequest $request, ActivityService $activityService): JsonResponse
    {
        return $this->executeAuthenticatedAction($request, fn(ActivityStatusRequest $req) =>
            $activityService->updateStatus(
                PsoContext::fromRequest($req),
                $req->activityStatus(),
                $req->input('data.resourceId'),
            )
        );
    }
}

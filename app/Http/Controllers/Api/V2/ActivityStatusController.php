<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V2\ActivityStatusRequest;
use App\Services\V2\ActivityService;
use App\Traits\V2\PSOAssistV2;
use Illuminate\Http\JsonResponse;

class ActivityStatusController extends Controller
{
    use PSOAssistV2;

    /**
     * Update the Task Status of an Activity.
     *
     * @response array{
     *   data: array|null,
     *   status: int,
     *   message: string,
     *   additionalDetails: array|null
     * }
     */
    public function update(ActivityStatusRequest $request): JsonResponse
    {
        return $this->executeAuthenticatedAction($request, function (ActivityStatusRequest $req) {
            $activityService = new ActivityService(
                $req->input('environment.token'),
                $req->validated(),
                $req->input('data.activityId'),
                $req->activityStatus(),
                $req->input('data.resourceId'),
            );

            return $activityService->updateStatus();
        });
    }
}

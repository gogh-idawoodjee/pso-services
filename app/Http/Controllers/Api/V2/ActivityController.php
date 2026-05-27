<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V2\ActivityDeleteRequest;
use App\Services\V2\ActivityService;
use App\Traits\V2\PSOAssistV2;
use Illuminate\Http\JsonResponse;

class ActivityController extends Controller
{
    use PSOAssistV2;

    /**
     * Generate One or More Activities.
     */
    public function store()
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(string $id)
    {
        //
    }

    /**
     * Delete Activity or Activities.
     *
     * Deletes one or more activities.
     *
     * @response array{
     *   data: array|null,
     *   status: int,
     *   message: string,
     *   additionalDetails: array|null
     * }
     */
    public function destroy(ActivityDeleteRequest $request): JsonResponse
    {
        return $this->executeAuthenticatedAction($request, function (ActivityDeleteRequest $req) {
            $activityService = new ActivityService(
                $req->input('environment.token'),
                $req->validated(),
            );

            return $activityService->deleteActivities();
        });
    }
}

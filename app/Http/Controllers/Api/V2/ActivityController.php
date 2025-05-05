<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V2\ActivityDeleteRequest;
use App\Services\V2\ActivityService;
use App\Traits\V2\PSOAssistV2;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActivityController extends Controller
{

    use PSOAssistV2;

    /**
     * Generate One or More Activities.
     */
    public function store(Request $request)
    {
        //
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Delete Activity or Activities
     */
    public function destroy(ActivityDeleteRequest $request): JsonResponse
    {
        return $this->executeAuthenticatedAction($request, function (ActivityDeleteRequest $req) {
            // so we have the token now in $req->input('environment.token')
            // we should send that the activity service? // all our services should accept a token
            $activityService = new ActivityService(
                $req->filled('environment.token') ? $req->input('environment.token') : null,
                $req->validated(),
            );

            return $activityService->deleteActivities();
        });
    }
}

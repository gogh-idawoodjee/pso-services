<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V2\ActivityRequest;
use App\Services\V2\ActivityService;
use App\Traits\V2\PSOAssistV2;
use Illuminate\Http\JsonResponse;


class ActivityStatusController extends Controller
{

    use PSOAssistV2;

    /**
     * Update the specified resource in storage.
     */
    public function update(ActivityRequest $request): JsonResponse
    {

//        return $this->getPSOToken($request->environment);

        return $this->executeAuthenticatedAction($request, function (ActivityRequest $req) {
            // so we have the token now in $req->input('environment.token')
            // we should send that the activity service? // all our services should accept a token
            $activityService = new ActivityService(
                $req->filled('environment.token') ? $req->input('environment.token') : null,
                $req->validated(),
                $req->input('data.activityId'),
                $req->activityStatus(),
                $req->input('data.resourceId')
            );

            return $activityService->updateStatus();
        });
    }


}

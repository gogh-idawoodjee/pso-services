<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V2\ScheduleExceptionRequest;
use App\Traits\V2\PSOAssistV2;
use Illuminate\Http\JsonResponse;


class ScheduleExceptionController extends Controller
{

    use PSOAssistV2;

    /**
     * Create a new custom Exception.
     */
    public function store(ScheduleExceptionRequest $request): JsonResponse
    {
        return $this->executeAuthenticatedAction($request, function (ScheduleExceptionRequest $req) {
            // so we have the token now in $req->input('environment.token')
            // we should send that the activity service? // all our services should accept a token
            $resourceShift = new ScheduleExceptionService(
                $req->filled('environment.token') ? $req->input('environment.token') : null,
                $req->validated(),
            );

            return $resourceShift->createException();
        });
    }


}

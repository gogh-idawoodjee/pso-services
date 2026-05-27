<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V2\ScheduleExceptionRequest;
use App\Services\V2\ScheduleExceptionService;
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
            $scheduleException = new ScheduleExceptionService(
                $req->input('environment.token'),
                $req->validated(),
            );

            return $scheduleException->createException();
        });
    }
}

<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V2\ResourceEventRequest;
use App\Services\V2\ResourceService;
use App\Traits\V2\PSOAssistV2;
use Illuminate\Http\JsonResponse;

class ResourceEventController extends Controller
{

    use PSOAssistV2;

    /**
     * Create a new resource event.
     */
    public function store(ResourceEventRequest $request): JsonResponse
    {
        return $this->executeAuthenticatedAction($request, function (ResourceEventRequest $req) {
            // so we have the token now in $req->input('environment.token')
            // we should send that the activity service? // all our services should accept a token
            $resourceService = new ResourceService(
                $req->filled('environment.token') ? $req->input('environment.token') : null,
                $req->validated(),
            );

            return $resourceService->createEvent();
        });
    }

}

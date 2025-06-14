<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V2\UnavailabilityRequest;
use App\Http\Requests\Api\V2\UnavailabilityUpdateRequest;
use App\Services\V2\ResourceService;
use App\Traits\V2\PSOAssistV2;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ResourceUnavailabilityController extends Controller
{

    use PSOAssistV2;

    // todo add timezone support

    /**
     * Create a new resource unavailability.
     */
    public function store(UnavailabilityRequest $request): JsonResponse
    {
        return $this->executeAuthenticatedAction($request, function (UnavailabilityRequest $req) {
            // so we have the token now in $req->input('environment.token')
            // we should send that the activity service? // all our services should accept a token
            $resourceUnavail = new ResourceService(
                $req->filled('environment.token') ? $req->input('environment.token') : null,
                $req->validated(),
            );

            return $resourceUnavail->createUnavailability();
        });
    }

    /**
     * Update one or more existing unavailabilities.
     */
    public function update(UnavailabilityUpdateRequest $request)
    {
        return $this->executeAuthenticatedAction($request, function (UnavailabilityUpdateRequest $req) {
            // so we have the token now in $req->input('environment.token')
            // we should send that the activity service? // all our services should accept a token
            $resourceUnavail = new ResourceService(
                $req->filled('environment.token') ? $req->input('environment.token') : null,
                $req->validated(),
            );

            return $resourceUnavail->updateUnavailablity();
        });
    }

}

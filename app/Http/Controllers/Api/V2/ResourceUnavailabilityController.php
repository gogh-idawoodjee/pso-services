<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V2\UnavailabilityRequest;
use App\Http\Requests\Api\V2\UnavailabilityUpdateRequest;
use App\Services\V2\ResourceService;
use App\Traits\V2\PSOAssistV2;
use Illuminate\Http\JsonResponse;

class ResourceUnavailabilityController extends Controller
{
    use PSOAssistV2;

    /**
     * Create a new resource unavailability.
     */
    public function store(UnavailabilityRequest $request): JsonResponse
    {
        return $this->executeAuthenticatedAction($request, function (UnavailabilityRequest $req) {
            $resourceUnavail = new ResourceService(
                $req->input('environment.token'),
                $req->validated(),
            );

            return $resourceUnavail->createUnavailability();
        });
    }

    /**
     * Update one or more existing unavailabilities.
     */
    public function update(UnavailabilityUpdateRequest $request): JsonResponse
    {
        return $this->executeAuthenticatedAction($request, function (UnavailabilityUpdateRequest $req) {
            $resourceUnavail = new ResourceService(
                $req->input('environment.token'),
                $req->validated(),
            );

            return $resourceUnavail->updateUnavailablity();
        });
    }
}

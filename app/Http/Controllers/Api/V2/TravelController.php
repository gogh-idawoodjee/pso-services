<?php

namespace App\Http\Controllers\Api\V2;


use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V2\TravelRequest;
use App\Services\V2\TravelService;
use App\Traits\V2\ApiResponses;
use App\Traits\V2\PSOAssistV2;

use Illuminate\Http\JsonResponse;
use JsonException;


class TravelController extends Controller

{

    use ApiResponses, PSOAssistV2;

    /**
     * @throws JsonException
     */
    public function store(TravelRequest $request)
    {


        return $this->executeAuthenticatedAction($request, function (TravelRequest $req) {
            // so we have the token now in $req->input('environment.token')
            // we should send that the activity service? // all our services should accept a token
            $travelService = new TravelService(
                $req->filled('environment.token') ? $req->input('environment.token') : null,
                $req->validated(),
            );

            return $travelService->process();
        });
    }

    public function update(): JsonResponse
    {

    }

    public function show(TravelRequest $request): JsonResponse|null
    {

        // returns an eloquent model of a travel object because we're storing this in the DB??

    }
}

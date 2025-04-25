<?php

namespace App\Http\Controllers\Api\V2;


use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V2\TravelRequest;
use App\Services\V2\TravelService;
use App\Traits\V2\ApiResponses;
use App\Traits\V2\PSOAssistV2;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class TravelController extends Controller

{

    use ApiResponses, PSOAssistV2;

    /**
     */
    public function store(TravelRequest $request): JsonResponse
    {

        try {
            $response = $this->getPSOToken($request->environment);

            if ($response->status() === 200) {
                // this is the authenticated response
                // so we've received our lats and longs, we need to do something with it
                $travelrequest = new TravelService($request);

            }

            if ($response->status() === 401) {
                return $response;
            }
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return $this->error('something totally funky went wrong', 500);
        }

    }

    public function update(): JsonResponse
    {

    }

    public function show(TravelRequest $request): JsonResponse|null
    {

        // returns an eloquent model of a travel object because we're storing this in the DB??

    }
}

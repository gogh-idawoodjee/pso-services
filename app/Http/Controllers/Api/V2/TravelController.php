<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V2\TravelRequest;
use App\Models\V2\PSOTravelLog;
use App\Services\V2\TravelService;
use App\Traits\V2\PSOAssistV2;
use Illuminate\Http\JsonResponse;
use Dedoc\Scramble\Attributes\ExcludeRouteFromDocs;
use Illuminate\Http\Request;

class TravelController extends Controller
{
    use PSOAssistV2;

    /**
     * Initiate the travel analysis.
     */
    public function store(TravelRequest $request): JsonResponse
    {
        return $this->executeAuthenticatedAction($request, function (TravelRequest $req) {
            $travelService = new TravelService(
                $req->input('environment.token'),
                $req->validated(),
            );

            return $travelService->process();
        });
    }

    /**
     * Receives the Travel Broadcast from PSO.
     */
    #[ExcludeRouteFromDocs]
    public function update(Request $request): JsonResponse
    {
        $travel = new TravelService(null, $request->toArray());
        $travel->receivePSOBroadcast();

        return $this->ok();
    }

    /**
     * Get the Details from the Analysis by ID
     */
    public function show(string $id): JsonResponse
    {
        $travelLog = PSOTravelLog::find($id);

        if ($travelLog) {
            $result = new TravelService(null, [$id]);

            return $this->ok($result->getTravelResults($travelLog), $travelLog->status->message());
        }

        return $this->error('Travel Log not found', 404);
    }
}

<?php

namespace App\Http\Controllers\Api\V2;

use App\DataTransferObjects\PsoContext;
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
    public function store(TravelRequest $request, TravelService $travelService): JsonResponse
    {
        return $this->executeAuthenticatedAction($request, fn(TravelRequest $req) =>
            $travelService->process(PsoContext::fromRequest($req))
        );
    }

    /**
     * Receives the Travel Broadcast from PSO.
     */
    #[ExcludeRouteFromDocs]
    public function update(Request $request, TravelService $travelService): JsonResponse
    {
        $travelService->receivePSOBroadcast($request->all());

        return $this->ok();
    }

    /**
     * Get the Details from the Analysis by ID
     */
    public function show(string $id, TravelService $travelService): JsonResponse
    {
        $travelLog = PSOTravelLog::find($id);

        if ($travelLog) {
            return $this->ok($travelService->getTravelResults($travelLog), $travelLog->status->message());
        }

        return $this->error('Travel Log not found', 404);
    }
}

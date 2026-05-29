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

/**
 * @group Travel Analyzer
 *
 * Asynchronous travel analysis via PSO.
 *
 * 1. POST to initiate — service forwards to PSO, returns a `resultsUrl` to poll
 * 2. PSO broadcasts results back to `/travelanalyzerservice` (internal webhook)
 * 3. GET the `resultsUrl` to collect results
 *
 * Optionally provide `data.callbackUrl` in the POST — results will be POSTed
 * to that URL automatically when PSO responds, eliminating the need to poll.
 *
 */
class TravelController extends Controller
{
    use PSOAssistV2;

    /**
     * Initiate the travel analysis.
     *
     * Sends travel coordinates to PSO. PSO processes asynchronously and broadcasts results
     * back to the service. Use the `resultsUrl` in the response to poll for results, or
     * provide `data.callbackUrl` to receive results via webhook.
     *
     * @response 200 scenario="Sent to PSO" {"data": {"payloadToPso": {"dsScheduleData": {"@xmlns": "http://360Scheduling.com/Schema/dsScheduleData.xsd", "Travel_Detail_Request": [{"id": "a1b2c3d4-e5f6-7890-abcd-ef1234567890", "latitude_from": 43.6511, "longitude_from": -79.3470, "latitude_to": 43.7001, "longitude_to": -79.4000}]}}, "responseFromPso": {}}, "status": 200, "message": "Successful. Sent to PSO", "additionalDetails": "To review results, please send a GET request to /api/v2/travelanalyzer/a1b2c3d4-e5f6-7890-abcd-ef1234567890", "resultsUrl": "/api/v2/travelanalyzer/a1b2c3d4-e5f6-7890-abcd-ef1234567890"}
     * @response 202 scenario="Dry run" {"data": {"payloadToPso": {"dsScheduleData": {"@xmlns": "http://360Scheduling.com/Schema/dsScheduleData.xsd", "Travel_Detail_Request": [{"id": "a1b2c3d4-e5f6-7890-abcd-ef1234567890", "latitude_from": 43.6511, "longitude_from": -79.3470, "latitude_to": 43.7001, "longitude_to": -79.4000}]}}}, "status": 202, "message": "Successful. Not sent to PSO by Request", "additionalDetails": "Please ensure environment.sendToPso is set to true to use the analyzer correctly"}
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
     *
     * Poll this endpoint after initiating a travel analysis. Results appear once PSO
     * broadcasts back (typically seconds to a minute).
     *
     * @response 200 scenario="Completed" {"data": {"travel_detail_request_id": "a1b2c3d4-e5f6-7890-abcd-ef1234567890", "start_address": "123 Queen St W, Toronto, ON", "end_address": "456 King St E, Toronto, ON", "pso": {"time": "00:25:00", "distance": "12.5 km"}, "google": {"time": "22 mins", "distance": "11.8 km"}}, "status": 200, "message": "Completed"}
     * @response 200 scenario="Pending" {"data": [], "status": 200, "message": "Sent"}
     * @response 404 scenario="Not Found" {"message": "Travel Log not found", "status": 404}
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

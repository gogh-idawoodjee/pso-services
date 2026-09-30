<?php

namespace App\Http\Controllers\Api\V2;

use App\DataTransferObjects\PsoContext;
use App\Http\Controllers\Controller;
use App\Http\OpenApi\ErrorResponse;
use App\Http\OpenApi\NotSentToPso;
use App\Http\OpenApi\OkResponse;
use App\Http\OpenApi\SentToPso;
use App\Http\Requests\Api\V2\TravelRequest;
use App\Models\V2\PSOTravelLog;
use App\Services\V2\TravelService;
use App\Traits\V2\PSOAssistV2;
use Dedoc\Scramble\Attributes\ExcludeRouteFromDocs;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 *
 * Asynchronous travel analysis via PSO.
 *
 * 1. POST to initiate — service forwards to PSO, returns a `resultsUrl` to poll
 * 2. PSO broadcasts results back to `/travelanalyzerservice` (internal webhook)
 * 3. GET the `resultsUrl` to collect results
 *
 * Optionally provide `data.callbackUrl` in the POST — results will be POSTed
 * to that URL automatically when PSO responds, eliminating the need to poll.
 */
#[Group('Travel Analyzer')]
class TravelController extends Controller
{
    use PSOAssistV2;

    /**
     * Initiate the travel analysis.
     *
     * Sends travel coordinates to PSO. PSO processes asynchronously and broadcasts results
     * back to the service. Use the `resultsUrl` in the response to poll for results, or
     * provide `data.callbackUrl` to receive results via webhook.
     */
    #[SentToPso(examples: [[
                    'payloadToPso' => [
                        'dsScheduleData' => [
                            '@xmlns' => 'http://360Scheduling.com/Schema/dsScheduleData.xsd',
                            'Travel_Detail_Request' => [
                                [
                                    'id' => 'a1b2c3d4-e5f6-7890-abcd-ef1234567890',
                                    'latitude_from' => 43.6511,
                                    'longitude_from' => -79.347,
                                    'latitude_to' => 43.7001,
                                    'longitude_to' => -79.4,
                                ],
                            ],
                        ],
                    ],
                    'responseFromPso' => new \stdClass,
                ]])]
    #[NotSentToPso(examples: [[
                    'payloadToPso' => [
                        'dsScheduleData' => [
                            '@xmlns' => 'http://360Scheduling.com/Schema/dsScheduleData.xsd',
                            'Travel_Detail_Request' => [
                                [
                                    'id' => 'a1b2c3d4-e5f6-7890-abcd-ef1234567890',
                                    'latitude_from' => 43.6511,
                                    'longitude_from' => -79.347,
                                    'latitude_to' => 43.7001,
                                    'longitude_to' => -79.4,
                                ],
                            ],
                        ],
                    ],
                ]])]
    public function store(TravelRequest $request, TravelService $travelService): JsonResponse
    {
        return $this->executeAuthenticatedAction($request, fn (TravelRequest $req) => $travelService->process(PsoContext::fromRequest($req))
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
     */
    #[OkResponse(data: 'array{travel_detail_request_id: string, start_address: string|null, end_address: string|null, pso: array{time: string|null, distance: string|null}, google: array{time: string|null, distance: string|null}, warnings?: list<string>}|list<mixed>', description: 'Travel results, or an empty list while PSO has not responded yet', examples: [
        ['data' => ['travel_detail_request_id' => 'a1b2c3d4-e5f6-7890-abcd-ef1234567890', 'start_address' => '123 Queen St W, Toronto, ON', 'end_address' => '456 King St E, Toronto, ON', 'pso' => ['time' => '00:25:00', 'distance' => '12.5 km'], 'google' => ['time' => '22 mins', 'distance' => '11.8 km']], 'message' => 'Completed'],
        ['data' => [], 'message' => 'The travel log has been sent but is awaiting a response from PSO.'],
    ])]
    #[ErrorResponse(404, 'Travel Log not found')]
    public function show(string $id, TravelService $travelService): JsonResponse
    {
        $travelLog = PSOTravelLog::find($id);

        if ($travelLog) {
            return $this->ok($travelService->getTravelResults($travelLog), $travelLog->status->message());
        }

        return $this->error('Travel Log not found', 404);
    }
}

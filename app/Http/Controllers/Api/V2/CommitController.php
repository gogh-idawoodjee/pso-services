<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Services\V2\CommitService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Commit
 */
class CommitController extends Controller
{
    /**
     * Commit a Suggested Dispatch broadcast from PSO.
     *
     * PSO calls this endpoint directly when it wants to commit a suggested
     * dispatch, so the request body is PSO's own broadcast shape rather than
     * the environment/data envelope used by every other V2 endpoint.
     * Authentication uses pre-configured server credentials, not a
     * caller-supplied token.
     *
     * @response 200 scenario="Committed" {"data": {"payloadToPso": {"dsScheduleData": {"@xmlns": "http://360Scheduling.com/Schema/dsScheduleData.xsd", "Activity_Status": [{"activity_id": "ACT-001", "status_id": "30", "resource_id": "RES-001"}]}}, "responseFromPso": {}}, "status": 200, "message": "Successful. Sent to PSO"}
     * @response 200 scenario="Nothing to commit" {"data": [], "status": 200, "message": "No suggested dispatch to commit"}
     */
    public function store(Request $request, CommitService $commitService): JsonResponse
    {
        return $commitService->commit($request->all());
    }
}

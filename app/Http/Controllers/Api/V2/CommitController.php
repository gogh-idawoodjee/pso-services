<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\V2\Environment;
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
     * the environment/data envelope used by every other V2 endpoint. The
     * commit_token in the URL is a per-Environment secret (generated when the
     * Environment is saved in pso-test-tools) — it identifies which PSO
     * instance to authenticate back to and doubles as the endpoint's auth,
     * since PSO itself supplies no credentials or token.
     *
     * @response 200 scenario="Committed" {"data": {"payloadToPso": {"dsScheduleData": {"@xmlns": "http://360Scheduling.com/Schema/dsScheduleData.xsd", "Activity_Status": [{"activity_id": "ACT-001", "status_id": "30", "resource_id": "RES-001"}]}}, "responseFromPso": {}}, "status": 200, "message": "Successful. Sent to PSO"}
     * @response 200 scenario="Nothing to commit" {"data": [], "status": 200, "message": "No suggested dispatch to commit"}
     */
    public function store(Request $request, Environment $environment, CommitService $commitService): JsonResponse
    {
        return $commitService->commit($environment, $request->all());
    }
}

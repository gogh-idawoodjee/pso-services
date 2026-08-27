<?php

namespace App\Http\Controllers\Api\V2;

use App\DataTransferObjects\PsoContext;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V2\RegionRequest;
use App\Services\V2\RegionService;
use App\Traits\V2\PSOAssistV2;
use Illuminate\Http\JsonResponse;

/**
 * @group System
 */
class RegionController extends Controller
{
    use PSOAssistV2;

    /**
     * Create Regions (RAM_Division)
     *
     * Creates one or more regions/divisions in the ARP modelling data. Unlike
     * scheduling payloads, this uses the DsModelling schema, not dsScheduleData.
     *
     * @response 200 scenario="Sent to PSO" {"data": {"payloadToPso": {"DsModelling": {"@xmlns": "http://360Scheduling.com/Schema/DsModelling.xsd", "RAM_Update": {"dataset_id": "dataset_123"}, "RAM_Division": [{"id": "NORTH", "send": true}]}}, "responseFromPso": {}}, "status": 200, "message": "Successful. Sent to PSO"}
     * @response 202 scenario="Dry run" {"data": {"payloadToPso": {"DsModelling": {"@xmlns": "http://360Scheduling.com/Schema/DsModelling.xsd", "RAM_Update": {"dataset_id": "dataset_123"}, "RAM_Division": [{"id": "NORTH", "send": true}]}}}, "status": 202, "message": "Successful. Not sent to PSO by Request"}
     */
    public function store(RegionRequest $request, RegionService $regionService): JsonResponse
    {
        return $this->executeAuthenticatedAction($request, fn (RegionRequest $req) => $regionService->createDivisions(PsoContext::fromRequest($req))
        );
    }
}

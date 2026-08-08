<?php

namespace App\Services\V2;

use App\Classes\V2\BaseService;
use App\DataTransferObjects\PsoContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

/**
 * Stub for the V2 port of the PSO load-test feature (see issue #36).
 *
 * The V1 version (PSOLoadTestService, removed) drove load by making real
 * HTTP round-trips back into this app's own V1 endpoints from a queued
 * job. That approach needs a redesign against V2's request/payload shape,
 * not a mechanical port, so this stub only validates input and reports
 * itself as not yet implemented.
 */
class LoadTestService extends BaseService
{
    public function run(PsoContext $context): JsonResponse
    {
        Log::info('V2 load test requested but not yet implemented', [
            'datasetId' => $context->datasetId(),
            'taskPrefix' => $context->data('taskPrefix'),
        ]);

        return $this->error('V2 load test is not yet implemented', 501);
    }
}

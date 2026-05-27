<?php

namespace App\Http\Controllers\Api\V2;

use App\DataTransferObjects\PsoContext;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V2\ResourceShiftRequest;
use App\Services\V2\ResourceService;
use App\Traits\V2\PSOAssistV2;
use Illuminate\Http\JsonResponse;

class ResourceShiftController extends Controller
{
    use PSOAssistV2;

    /**
     * Update the specified resource shift.
     *
     * If this is a change to a shift in the ARP, after a successful transaction,
     * the API will update the resource's shift and send the updated rota to the DSE
     * (Rota Update) so the change is immediately recognized by the optimization process.
     */
    public function update(ResourceShiftRequest $request, ResourceService $resourceService): JsonResponse
    {
        return $this->executeAuthenticatedAction($request, fn(ResourceShiftRequest $req) =>
            $resourceService->updateShift(PsoContext::fromRequest($req))
        );
    }
}

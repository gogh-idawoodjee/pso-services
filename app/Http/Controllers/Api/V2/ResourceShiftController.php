<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
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
    public function update(ResourceShiftRequest $request): JsonResponse
    {
        return $this->executeAuthenticatedAction($request, function (ResourceShiftRequest $req) {
            // so we have the token now in $req->input('environment.token')
            // we should send that the activity service? // all our services should accept a token
            $resourceShift = new ResourceService(
                $req->filled('environment.token') ? $req->input('environment.token') : null,
                $req->validated(),
            );

            return $resourceShift->updateShift();
        });
    }


}

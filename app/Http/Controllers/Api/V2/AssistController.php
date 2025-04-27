<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V2\DeleteObjectRequest;
use App\Http\Requests\Api\V2\LoadPsoRequest;
use App\Services\V2\DeleteService;
use App\Services\V2\LoadService;
use App\Traits\V2\PSOAssistV2;
use Illuminate\Http\JsonResponse;

class AssistController extends Controller
{
    //
    use PSOAssistV2;

    public function destroy(DeleteObjectRequest $request): JsonResponse
    {
        return $this->executeAuthenticatedAction($request, function (DeleteObjectRequest $req) {
            // so we have the token now in $req->input('environment.token')
            // we should send that the activity service? // all our services should accept a token
            $deleteService = new DeleteService(
                $req->filled('environment.token') ? $req->input('environment.token') : null,
                $req->input('data')
            );

            return $deleteService->deleteObject();
        });
    }

    public function store(LoadPsoRequest $request): JsonResponse// initialize PSO
    {

        return $this->executeAuthenticatedAction($request, function (LoadPsoRequest $req) {
            // so we have the token now in $req->input('environment.token')
            // we should send that the activity service? // all our services should accept a token
            $loadService = new LoadService(
                $req->filled('environment.token') ? $req->input('environment.token') : null,
                $req->validated()
            );

            return $loadService->loadPSO();
        });

    }

    public function update(UpdateRotaRequest $request): JsonResponse// update rota
    {
        return $this->executeAuthenticatedAction($request, function (UpdateRotaRequest $req) {
            // so we have the token now in $req->input('environment.token')
            // we should send that the activity service? // all our services should accept a token
            $loadService = new LoadService(
                $req->filled('environment.token') ? $req->input('environment.token') : null,
                $req->validated()
            );

            return $loadService->updateRota();
        });
    }
}

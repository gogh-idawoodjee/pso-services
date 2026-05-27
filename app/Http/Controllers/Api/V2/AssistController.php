<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V2\DeleteObjectRequest;
use App\Http\Requests\Api\V2\LoadPsoRequest;
use App\Http\Requests\Api\V2\SystemUsageRequest;
use App\Http\Requests\Api\V2\UpdateRotaRequest;
use App\Services\V2\AssistService;
use App\Services\V2\DeleteService;
use App\Services\V2\LoadService;
use App\Traits\V2\PSOAssistV2;
use Illuminate\Http\JsonResponse;

class AssistController extends Controller
{
    use PSOAssistV2;

    /**
     * Get System Usage
     */
    public function show(SystemUsageRequest $request): JsonResponse
    {
        return $this->executeAuthenticatedAction($request, function (SystemUsageRequest $req) {
            $assistService = new AssistService(
                $req->input('environment.token'),
                $req->validated(),
            );

            $datasetId = $req->headers->get('datasetId');
            $baseUrl = $req->headers->get('baseUrl');

            return $assistService->getSystemusage($datasetId, $baseUrl);
        });
    }

    /**
     * Generic Delete Service
     */
    public function destroy(DeleteObjectRequest $request): JsonResponse
    {
        return $this->executeAuthenticatedAction($request, function (DeleteObjectRequest $req) {
            $deleteService = new DeleteService(
                $req->input('environment.token'),
                $req->validated(),
            );

            return $deleteService->deleteObject();
        });
    }

    /**
     * Initialize PSO
     */
    public function store(LoadPsoRequest $request): JsonResponse
    {
        return $this->executeAuthenticatedAction($request, function (LoadPsoRequest $req) {
            $loadService = new LoadService(
                $req->input('environment.token'),
                $req->validated(),
            );

            return $loadService->loadPSO();
        });
    }

    /**
     * Send Rota to DSE
     */
    public function update(UpdateRotaRequest $request): JsonResponse
    {
        return $this->executeAuthenticatedAction($request, function (UpdateRotaRequest $req) {
            $loadService = new LoadService(
                $req->input('environment.token'),
                $req->validated(),
            );

            return $loadService->updateRota();
        });
    }
}

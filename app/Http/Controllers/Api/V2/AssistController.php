<?php

namespace App\Http\Controllers\Api\V2;

use App\DataTransferObjects\PsoContext;
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
    public function show(SystemUsageRequest $request, AssistService $assistService): JsonResponse
    {
        return $this->executeAuthenticatedAction($request, fn(SystemUsageRequest $req) =>
            $assistService->getSystemUsage(PsoContext::fromRequest($req))
        );
    }

    /**
     * Generic Delete Service
     */
    public function destroy(DeleteObjectRequest $request, DeleteService $deleteService): JsonResponse
    {
        return $this->executeAuthenticatedAction($request, fn(DeleteObjectRequest $req) =>
            $deleteService->deleteObject(PsoContext::fromRequest($req))
        );
    }

    /**
     * Initialize PSO
     */
    public function store(LoadPsoRequest $request, LoadService $loadService): JsonResponse
    {
        return $this->executeAuthenticatedAction($request, fn(LoadPsoRequest $req) =>
            $loadService->loadPSO(PsoContext::fromRequest($req))
        );
    }

    /**
     * Send Rota to DSE
     */
    public function update(UpdateRotaRequest $request, LoadService $loadService): JsonResponse
    {
        return $this->executeAuthenticatedAction($request, fn(UpdateRotaRequest $req) =>
            $loadService->updateRota(PsoContext::fromRequest($req))
        );
    }
}

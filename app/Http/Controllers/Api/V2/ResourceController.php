<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V2\ShowResourceRequest;
use App\Services\V2\ResourceService;
use App\Traits\V2\PSOAssistV2;
use Illuminate\Http\JsonResponse;
use JsonException;

class ResourceController extends Controller
{

    use PSOAssistV2;

    /**
     * Display the specified resource.
     * @throws JsonException
     */
    public function show(ShowResourceRequest $request, string $resourceId): JsonResponse
    {

        return $this->executeAuthenticatedAction($request, function (ShowResourceRequest $req) use ($resourceId) {
            // so we have the token now in $req->input('environment.token')
            // we should send that the activity service? // all our services should accept a token
            $resourceService = new ResourceService(
                $req->filled('environment.token') ? $req->input('environment.token') : null,
                $req->validated(),
            );

            $datasetId = $req->headers->get('datasetId');

            $baseUrl = $req->headers->get('baseUrl');
            return $resourceService->getResource($datasetId, $resourceId, $baseUrl);
        });

    }

}

<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V2\ResourceRequest;
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
    public function show(ResourceRequest $request, string $resourceId): JsonResponse
    {

        return $this->executeAuthenticatedAction($request, function (ResourceRequest $req) use ($resourceId) {

            $resourceService = new ResourceService(
                $req->filled('environment.token') ? $req->input('environment.token') : $req->headers->get('token'),

                $req->validated(),
            );

            $datasetId = $req->headers->get('datasetId');

            $baseUrl = $req->headers->get('baseUrl');
            return $resourceService->getResource($datasetId, $resourceId, $baseUrl);
        });

    }

    /**
     * @throws JsonException
     */
    public function index(ResourceRequest $request): JsonResponse
    {
        return $this->executeAuthenticatedAction($request, function (ResourceRequest $req) {
            // so we have the token now in $req->input('environment.token')
            // we should send that the activity service? // all our services should accept a token
            $resourceService = new ResourceService(
                $req->filled('environment.token') ? $req->input('environment.token') : $req->headers->get('token'),
                $req->validated(),
            );

            $datasetId = $req->headers->get('datasetId');

            $baseUrl = $req->headers->get('baseUrl');
            return $this->ok(['resources' => $resourceService->getResourceList($datasetId, $baseUrl)->toSelectOptions()->getSelectOptions()]);
        });

    }

}

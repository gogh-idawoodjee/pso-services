<?php

namespace App\Http\Controllers\Api\V2;


use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V2\HealthCheckRequest;
use App\Traits\ApiResponses;
use App\Traits\PSOAssistV2;
use Illuminate\Http\JsonResponse;
use JsonException;

class HealthCheckController extends Controller

{

    use ApiResponses, PSOAssistV2;

    /**
     * @throws JsonException
     */
    public function check(HealthCheckRequest $request): JsonResponse|null
    {

        return $this->getPSOToken($request->environment);

    }
}

<?php

namespace App\Http\Controllers\Api\V2;

use App\Helpers\PSOHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V2\HealthCheckRequest;
use App\Traits\ApiResponses;
use App\Traits\PSOAssistV2;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class HealthCheckController extends Controller

{

    use ApiResponses, PSOAssistV2;

    public function check(HealthCheckRequest $request)
    {


        return $this->getPSOToken($request->environment);

        return $this->ok('health is checked', $request->all());
    }
}

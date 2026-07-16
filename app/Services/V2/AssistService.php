<?php

namespace App\Services\V2;

use App\Classes\V2\BaseService;
use App\DataTransferObjects\PsoContext;
use App\Enums\PsoEndpointSegment;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class AssistService extends BaseService
{
    public function getSystemUsage(PsoContext $context, string|null $minDate = null, string|null $maxDate = null): JsonResponse
    {
        if ($minDate === null) {
            $minDate = Carbon::now()->toIso8601String();
            $maxDate = Carbon::now()->addDay()->toIso8601String();
        }

        return $this->psoClient->getPsoData(
            $context->datasetId(),
            $context->baseUrl(),
            $context->token,
            PsoEndpointSegment::USAGE,
            minDate: $minDate,
            maxDate: $maxDate,
        );
    }
}

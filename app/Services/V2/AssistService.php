<?php

namespace App\Services\V2;

use App\Classes\V2\BaseService;
use App\Enums\PsoEndpointSegment;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use JsonException;

class AssistService extends BaseService
{


    /**
     * @throws JsonException
     */
    public function getSystemusage(string|null $datasetId, string|null $baseUrl, string|null $minDate = null, string|null $maxDate = null): JsonResponse
    {

        if ($minDate === null) {
            $minDate = Carbon::now()->toIso8601String();
            $maxDate = Carbon::now()->addDay()->toIso8601String();
        }

        return $this->getPsoData(
            $datasetId,
            $baseUrl,
            $this->sessionToken,
            PsoEndpointSegment::USAGE,
            null,
            false,
            false,
            $minDate,
            $maxDate
        );

    }
}

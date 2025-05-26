<?php

namespace App\Services\V2;

use App\Classes\V2\BaseService;
use App\Enums\PsoEndpointSegment;
use Date;
use Illuminate\Http\JsonResponse;
use JsonException;

class AssistService extends BaseService
{


    /**
     * @throws JsonException
     */
    public function getSystemusage(string|null $datasetId, string|null $baseUrl, date|null $minDate = null, date|null $maxDate = null): JsonResponse
    {

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

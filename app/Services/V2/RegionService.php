<?php

namespace App\Services\V2;

use App\Classes\V2\BaseService;
use App\Classes\V2\EntityBuilders\RamUpdateBuilder;
use App\DataTransferObjects\PsoContext;
use App\Enums\PsoEndpointSegment;
use App\Helpers\Stubs\Region;
use App\Helpers\Stubs\RegionType;
use Exception;
use Illuminate\Http\JsonResponse;

class RegionService extends BaseService
{
    public function createDivisions(PsoContext $context): JsonResponse
    {
        try {
            $datasetId = $context->datasetId();
            $regionIds = $context->data('regions', []);
            $descriptions = $context->data('descriptions');
            $regionParent = $context->data('regionParent');
            $regionCategory = $context->data('regionCategory');
            $send = $context->data('send', true);

            $useDescriptions = is_array($descriptions) && count($descriptions) === count($regionIds);

            $divisions = collect($regionIds)
                ->map(fn ($regionId, $index) => Region::makeRAMDivision(
                    regionId: $regionId,
                    description: $useDescriptions ? $descriptions[$index] : null,
                    send: $send,
                    ramDivisionId: $regionParent,
                    ramDivisionTypeId: $regionCategory,
                ))
                ->values()
                ->all();

            $payload = [
                'RAM_Update' => RamUpdateBuilder::make($datasetId)
                    ->description('Add '.count($divisions).' region(s) to ARP')
                    ->build(),
                'RAM_Division' => count($divisions) === 1 ? $divisions[0] : $divisions,
            ];

            if ($regionCategory) {
                $payload['RAM_Division_Type'] = RegionType::make($regionCategory);
            }

            if ($context->token) {
                $psoPayload = $this->psoClient->buildModellingPayload($payload);

                $psoResponse = $this->psoClient->sendToPso(
                    $psoPayload,
                    $context->environment(),
                    $context->token,
                    PsoEndpointSegment::DATA,
                );

                if ($psoResponse->status() >= 400) {
                    return $psoResponse;
                }

                return $this->sentToPso([
                    'payloadToPso' => $this->psoClient->buildModellingPayload($payload, true),
                    'responseFromPso' => $psoResponse->getData(),
                ]);
            }

            return $this->notSentToPso($this->psoClient->buildModellingPayload($payload, true));
        } catch (Exception $e) {
            $this->logError($e, __METHOD__, __CLASS__);

            return $this->error('An unexpected error occurred', 500);
        }
    }
}

<?php

namespace App\Services\V2;

use App\Classes\V2\BaseService;
use App\Classes\V2\PsoClient;
use App\Classes\V2\EntityBuilders\ActivityBuilder;
use App\Classes\V2\EntityBuilders\ActivityStatusBuilder;
use App\Classes\V2\EntityBuilders\ResourceEventBuilder;
use App\Classes\V2\EntityBuilders\ShiftBuilder;
use App\Classes\V2\Formatters\ResourceFormatter;
use App\Constants\PSOConstants;
use App\DataTransferObjects\PsoContext;
use App\Enums\ActivityClass;
use App\Enums\ActivityStatus;
use App\Enums\EventType;
use App\Enums\PsoEndpointSegment;
use App\Enums\ShiftEntity;
use App\Helpers\Stubs\RamTimePattern;
use App\Helpers\Stubs\RamUnavailability;
use App\Helpers\Stubs\RamUpdate;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;

class ResourceService extends BaseService
{
    public function createEvent(PsoContext $context): JsonResponse
    {
        try {
            $payload = ResourceEventBuilder::make($context->data('resourceId'), EventType::from($context->data('eventType')))
                ->eventDateTime($context->data('eventDateTime'))
                ->latitude($context->data('lat'))
                ->longitude($context->data('long'))
                ->build();

            return $this->psoClient->sendOrSimulateBuilder()
                ->payload(['Schedule_Event' => $payload])
                ->environment($context->environment())
                ->psoApiVersion($context->psoApiVersion())
                ->token($context->token)
                ->includeInputReference('Created Event')
                ->send();
        } catch (Exception $e) {
            $this->logError($e, __METHOD__, __CLASS__);
            return $this->error('An unexpected error occurred', 500);
        }
    }

    public function updateShift(PsoContext $context): JsonResponse
    {
        try {
            $payload = ShiftBuilder::make()
                ->shiftId($context->data('shiftId'))
                ->shiftType($context->data('shiftType'))
                ->startDateTime($context->data('startDateTime'))
                ->endDateTime($context->data('endDateTime'))
                ->arpObject((bool) $context->data('isArpObject'))
                ->description($context->data('description'))
                ->manualSchedulingOnly((bool) $context->data('turnManualSchedulingOn'))
                ->rotaId($context->data('rotaId'))
                ->resourceId($context->data('resourceId'))
                ->build();

            $entity = $context->data('isArpObject') ? ShiftEntity::RAMROTAITEM->value : ShiftEntity::SHIFT->value;

            return $this->psoClient->sendOrSimulateBuilder()
                ->payload([$entity => $payload])
                ->environment($context->environment())
                ->token($context->token)
                ->requiresRotaUpdate(true, 'Updated Rota After Shift Update')
                ->psoApiVersion($context->psoApiVersion())
                ->send();
        } catch (Exception $e) {
            $this->logError($e, __METHOD__, __CLASS__);
            return $this->error('An unexpected error occurred', 500);
        }
    }

    public function updateUnavailability(PsoContext $context): JsonResponse
    {
        try {
            $data = $context->data();
            $unavailabilityIds = (array) data_get($data, 'unavailabilityIds', []);
            $isMassUpdate = count($unavailabilityIds) > 1;

            $timePatternId = Str::uuid()->getHex();

            $ramTimePattern = RamTimePattern::make(
                $timePatternId,
                data_get($data, 'baseDateTime'),
                data_get($data, 'duration'),
            );

            $ramUnavailabilities = collect($unavailabilityIds)
                ->map(static fn() => RamUnavailability::make(
                    data_get($data, 'resourceId'),
                    $timePatternId,
                    data_get($data, 'categoryId'),
                    data_get($data, 'description'),
                ))
                ->all();

            $description = ($isMassUpdate ? 'Mass ' : '') . 'Update Unavailability via ' . PSOConstants::APP_INSTANCE_ID;

            $payload = [
                'RAM_Update' => RamUpdate::make($context->datasetId(), $description),
                'RAM_Unavailability' => $isMassUpdate ? $ramUnavailabilities : $ramUnavailabilities[0],
                'RAM_Time_Pattern' => $ramTimePattern,
            ];

            return $this->psoClient->sendOrSimulateBuilder()
                ->payload($payload)
                ->environment($context->environment())
                ->psoApiVersion($context->psoApiVersion())
                ->token($context->token)
                ->includeInputReference($description)
                ->requiresRotaUpdate(true, 'Updated Rota After Unavailability Update')
                ->send();
        } catch (Exception $e) {
            $this->logError($e, __METHOD__, __CLASS__);
            return $this->error('An unexpected error occurred', 500);
        }
    }

    public function createUnavailability(PsoContext $context): JsonResponse
    {
        try {
            if ($context->data('isArpObject')) {
                $payload = $this->buildArpUnavailability($context);

                return $this->psoClient->sendOrSimulateBuilder()
                    ->payload($payload)
                    ->environment($context->environment())
                    ->psoApiVersion($context->psoApiVersion())
                    ->token($context->token)
                    ->includeInputReference('send unavailability to ARP')
                    ->requiresRotaUpdate(true)
                    ->send();
            }

            $activityId = Uuid::uuid4()->toString();

            // Build the full data array for ActivityBuilder (it expects the nested structure)
            $builderData = $context->validated;
            data_set($builderData, 'data.activityId', $activityId);

            $payload = ActivityBuilder::make($builderData)
                ->withActivityClass(ActivityClass::PRIVATE)
                ->withActivityStatusBuilder(
                    ActivityStatusBuilder::make($activityId, ActivityStatus::COMMITTED)
                        ->resourceId($context->data('resourceId'))
                        ->fixed(true)
                        ->dateTimeFixed($context->data('baseDateTime'))
                        ->duration($context->data('duration'))
                )
                ->build();

            return $this->psoClient->sendOrSimulateBuilder()
                ->payload($payload)
                ->environment($context->environment())
                ->psoApiVersion($context->psoApiVersion())
                ->token($context->token)
                ->includeInputReference('Created Unavailability')
                ->send();
        } catch (Exception $e) {
            $this->logError($e, __METHOD__, __CLASS__);
            return $this->error('An unexpected error occurred', 500);
        }
    }

    private function buildArpUnavailability(PsoContext $context): array
    {
        $data = $context->data();
        $timePatternId = Str::uuid()->getHex();

        $ramTimePattern = RamTimePattern::make(
            $timePatternId,
            data_get($data, 'baseDateTime'),
            data_get($data, 'duration'),
        );
        $ramUnavailability = RamUnavailability::make(
            data_get($data, 'resourceId'),
            $timePatternId,
            data_get($data, 'categoryId'),
            data_get($data, 'description'),
        );

        return [
            'RAM_Update' => RamUpdate::make($context->datasetId(), 'Create Unavailability via ' . PSOConstants::APP_INSTANCE_ID),
            'RAM_Unavailability' => $ramUnavailability,
            'RAM_Time_Pattern' => $ramTimePattern,
        ];
    }

    public function getResource(PsoContext $context, string $resourceId): JsonResponse
    {
        $resource = $this->psoClient->getPsoData(
            $context->datasetId(),
            $context->baseUrl(),
            $context->token,
            PsoEndpointSegment::RESOURCE,
            $resourceId,
        )->getData(true);

        $formatted = ResourceFormatter::format($resource, $resourceId);

        if ($formatted === null) {
            return $this->error('Resource not found', 404);
        }

        return $this->ok($formatted);
    }

    public function getResourceSelectOptions(PsoContext $context): array
    {
        $rawData = $this->psoClient->getPsoData(
            $context->datasetId(),
            $context->baseUrl(),
            $context->token,
            PsoEndpointSegment::DATA,
            includeInput: true,
        )->getData(true);

        $rootKey = PsoClient::resolveScheduleDataKey($rawData);
        $resources = data_get($rawData, "{$rootKey}.Resources", []);

        $selectOptions = [];
        foreach ($resources as $resource) {
            $id = data_get($resource, 'id');
            $displayName = trim(data_get($resource, 'first_name', '') . ' ' . data_get($resource, 'surname', ''));

            if (empty($displayName)) {
                $displayName = $id ?? 'Unknown Resource';
            }

            $selectOptions[$id] = $displayName;
        }

        return $selectOptions;
    }
}

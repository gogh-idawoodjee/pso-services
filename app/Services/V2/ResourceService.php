<?php

namespace App\Services\V2;

use App\Classes\V2\BaseService;
use App\Classes\V2\PsoClient;
use App\Classes\V2\EntityBuilders\ActivityBuilder;
use App\Classes\V2\EntityBuilders\ActivityStatusBuilder;
use App\Classes\V2\EntityBuilders\ResourceEventBuilder;
use App\Classes\V2\EntityBuilders\ShiftBuilder;
use App\DataTransferObjects\PsoContext;
use App\Enums\ActivityClass;
use App\Enums\ActivityStatus;
use App\Enums\EventType;
use App\Enums\PsoEndpointSegment;
use App\Enums\ShiftEntity;
use App\Helpers\LocationHelper;
use App\Helpers\Stubs\RamTimePattern;
use App\Helpers\Stubs\RamUnavailability;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Carbon\CarbonInterval;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;
use Throwable;

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
                ->arpObject($context->data('isArpObject'))
                ->description($context->data('description'))
                ->manualSchedulingOnly($context->data('isManualSchedulingOnly'))
                ->rotaId($context->data('rotaId'))
                ->resourceId($context->data('resourceId'))
                ->build();

            $entity = $context->data('isArpObject') ? ShiftEntity::RAMROTAITEM->value : ShiftEntity::SHIFT->value;

            return $this->psoClient->sendOrSimulate(
                [$entity => $payload],
                $context->environment(),
                $context->token,
                true,
                'Updated Rota After Shift Update',
                psoApiVersion: $context->psoApiVersion(),
            );
        } catch (Exception $e) {
            $this->logError($e, __METHOD__, __CLASS__);
            return $this->error('An unexpected error occurred', 500);
        }
    }

    public function updateUnavailability(PsoContext $context): JsonResponse|null
    {
        try {
            // TODO: implement unavailability update
            $unavailabilities = $context->data('unavailability_id');
        } catch (Exception $e) {
            $this->logError($e, __METHOD__, __CLASS__);
            return $this->error('An unexpected error occurred', 500);
        }

        return null;
    }

    public function createUnavailability(PsoContext $context): JsonResponse
    {
        try {
            if ($context->data('isArpObject')) {
                $payload = $this->buildArpUnavailability($context->data());

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

    private function buildArpUnavailability(array $data): array
    {
        $timePatternId = Str::uuid()->getHex();

        $timepattern = RamTimePattern::make(
            data_get($data, 'resourceId'),
            $timePatternId,
            data_get($data, 'categoryId'),
        );
        $unavailability = RamUnavailability::make(
            $timePatternId,
            data_get($data, 'baseDateTime'),
            data_get($data, 'duration'),
        );

        return ['Ram_Time_Pattern' => $timepattern, 'RAM_Unavailability' => $unavailability];
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

        $rootKey = PsoClient::resolveScheduleDataKey($resource);

        $resourceData = data_get($resource, "{$rootKey}.Resources");
        $resourceTypeId = data_get($resource, "{$rootKey}.Resources.resource_type_id");
        $resourceType = collect(data_get($resource, "{$rootKey}.Resource_Type", []))
            ->firstWhere('id', $resourceTypeId);

        if ($resourceData) {
            $sameStartAndEndLocation = data_get($resourceData, 'location_id_start') === data_get($resourceData, 'location_id_end');

            $locationStart = LocationHelper::findLocationById($resource, data_get($resourceData, 'location_id_start'));
            $locationEnd = LocationHelper::findLocationById($resource, data_get($resourceData, 'location_id_end'));
            $googleLocationStart = LocationHelper::formatAddress(data_get($locationStart, 'latitude'), data_get($locationStart, 'longitude'));
            if ($sameStartAndEndLocation) {
                $googleLocationEnd = $googleLocationStart;
            } else {
                $googleLocationEnd = LocationHelper::formatAddress(data_get($locationEnd, 'latitude'), data_get($locationEnd, 'longitude'));
            }

            $formatted_resource = [
                'resource' => [
                    'personal' => [
                        'full_name' => data_get($resource, "{$rootKey}.Resources.first_name") . ' ' . data_get($resource, "{$rootKey}.Resources.surname"),
                        'first_name' => data_get($resource, "{$rootKey}.Resources.first_name"),
                        'surname' => data_get($resource, "{$rootKey}.Resources.surname"),
                    ],
                    'additional_attributes' => $this->getAdditionalAttributes($resourceId, data_get($resource, "{$rootKey}.Additional_Attribute")),
                    'resource_id' => data_get($resource, "{$rootKey}.Resources.id"),
                    'resource_type' => [
                        'type_id' => data_get($resourceData, 'resource_type_id'),
                        'description' => data_get($resourceType, 'description'),
                    ],
                    'note' => data_get($resource, "{$rootKey}.Resources.memo"),
                    'max_travel' => $this->resourceMaxTravel($resourceData, $resourceType, 'max_travel'),
                    'max_travel_outside_shift_to_first_activity' => $this->resourceMaxTravel($resourceData, $resourceType, 'travel_from'),
                    'max_travel_outside_shift_to_home' => $this->resourceMaxTravel($resourceData, $resourceType, 'travel_to'),
                    'location' => [
                        'same_start_and_end' => $sameStartAndEndLocation,
                        'google_reverse_geocode_lookup' => [
                            'start' => $googleLocationStart,
                            'end' => $googleLocationEnd,
                        ],
                        'pso' => [
                            'start' => LocationHelper::formatPsoAddress($locationStart),
                            'end' => LocationHelper::formatPsoAddress($locationEnd),
                        ],
                    ],
                    'regions' => $this->getRelatedItemsForResource($resource, $resourceId, 'region', $rootKey),
                    'skills' => $this->getRelatedItemsForResource($resource, $resourceId, 'skill', $rootKey),
                    'shifts' => $this->getResourceShiftsFormatted(data_get($resource, "{$rootKey}.Shift"), data_get($resource, "{$rootKey}.Plan_Route")),
                ],
            ];

            return $this->ok($formatted_resource);
        }

        return $this->error('Resource not found', 404);
    }

    private function resourceMaxTravel(array|null $resourceData, array|null $resourceType, string $key): array
    {
        $maxTravelRaw = data_get($resourceData, $key);
        $maxTravelFallback = data_get($resourceType, $key);

        if ($maxTravelRaw !== null && $maxTravelRaw !== '') {
            $maxTravelValue = $maxTravelRaw;
            $maxTravelSource = 'property of resource';
        } elseif ($maxTravelFallback !== null && $maxTravelFallback !== '') {
            $maxTravelValue = $maxTravelFallback;
            $maxTravelSource = 'inherited from resource_type';
        } else {
            $maxTravelValue = null;
            $maxTravelSource = 'none';
        }

        $maxTravelFormatted = null;
        if ($maxTravelValue) {
            try {
                $maxTravelFormatted = CarbonInterval::fromString($maxTravelValue)->forHumans(['options' => CarbonInterface::FLOOR]);
            } catch (Exception) {
            }
        }

        return [
            'value' => $maxTravelValue,
            'source' => $maxTravelSource,
            'formatted' => $maxTravelFormatted,
        ];
    }

    private function getRelatedItemsForResource(array $data, string|int $resourceId, string $resourceEntity, string $rootKey = 'dsScheduleData'): array
    {
        [$relatedEntityKey, $entityKey, $entityListKey] = match ($resourceEntity) {
            'region' => ["{$rootKey}.Resource_Region", 'region_id', "{$rootKey}.Region"],
            'skill'  => ["{$rootKey}.Resource_Skill", 'skill_id', "{$rootKey}.Skill"],
            default  => [null, null, null],
        };

        if (!$relatedEntityKey || !$entityKey || !$entityListKey) {
            return [];
        }

        $entityRelations = collect(data_get($data, $relatedEntityKey, []));
        $entityList      = collect(data_get($data, $entityListKey, []));

        $entityIds = $entityRelations
            ->where('resource_id', (string) $resourceId)
            ->pluck($entityKey)
            ->unique()
            ->values();

        return $entityList
            ->whereIn('id', $entityIds)
            ->map(static fn($entity) => [
                'id'          => data_get($entity, 'id'),
                'description' => data_get($entity, 'description'),
            ])
            ->values()
            ->tap(static fn($collection) => $collection->push(['total' => $collection->count()]))
            ->all();
    }

    private function getAdditionalAttributes(string $resourceId, array|null $additionalAttributes = null): array
    {
        $attributeList = [];

        if (empty($additionalAttributes)) {
            return $attributeList;
        }

        foreach ($additionalAttributes as $attribute) {
            if (data_get($attribute, 'resource_id') !== $resourceId) {
                continue;
            }
            $label = data_get($attribute, 'label');
            $value = data_get($attribute, 'label_value');
            $attributeList[$label] = $value;
        }

        return $attributeList;
    }

    private function getResourceShiftsFormatted(array|null $shifts, array|null $routes): Collection
    {
        $shifts = $shifts ?? [];
        $routes = $routes ?? [];

        $groupedRoutes = collect($routes)->groupBy('shift_id');

        $safeParse = static function (string|null $dt): Carbon|null {
            if (!$dt) {
                return null;
            }
            try {
                return Carbon::parse($dt);
            } catch (Throwable) {
                return null;
            }
        };

        $parseInterval = static function ($interval): string {
            if (!$interval) {
                return 'N/A';
            }
            try {
                return CarbonInterval::fromString($interval)
                    ->forHumans(['options' => CarbonInterface::FLOOR]);
            } catch (Throwable) {
                return 'N/A';
            }
        };

        $formattedShifts = collect($shifts)->map(function ($shift) use ($safeParse, $groupedRoutes, $parseInterval) {
            $start = $safeParse(data_get($shift, 'start_datetime'));
            $end   = $safeParse(data_get($shift, 'end_datetime'));
            $shiftId = data_get($shift, 'id');

            $shiftDate = $start?->toFormattedDateString();
            $shiftSpan = ($start && $end)
                ? $start->format('H:i') . ' - ' . $end->format('H:i')
                : 'N/A';
            $shiftDuration = ($start && $end) ? $start->diffInHours($end) : 0;

            $routeData = $groupedRoutes->get($shiftId)?->first() ?? [];

            $overtimePeriod = Arr::has($shift, 'overtime_period')
                ? $parseInterval(data_get($shift, 'overtime_period'))
                : 'no overtime';

            $utilisation = [
                'percent'                 => data_get($routeData, 'utilisation', 0),
                'total_unutilised_time'   => $parseInterval(data_get($routeData, 'total_unutilised_time')),
                'total_private_time'      => $parseInterval(data_get($routeData, 'total_private_time')),
                'total_break_time'        => $parseInterval(data_get($routeData, 'total_break_time')),
                'total_on_site_time'      => $parseInterval(data_get($routeData, 'total_on_site_time')),
                'total_travel_time'       => $parseInterval(data_get($routeData, 'total_travel_time')),
                'average_travel_time'     => $parseInterval(data_get($routeData, 'average_travel_time')),
                'total_allocations'       => data_get($routeData, 'total_allocations', 0),
                'route_margin'            => data_get($routeData, 'route_margin', 0),
            ];

            $shiftCollection = collect($shift)
                ->put('shift_date', $shiftDate)
                ->put('shift_span', $shiftSpan)
                ->put('shift_duration', $shiftDuration)
                ->put('overtime_period', $overtimePeriod)
                ->put('utilisation', $utilisation);

            $keysToRemove = ['start_datetime', 'end_datetime', 'actual', 'split_allowed', 'resource_id'];
            $shiftCollection = $shiftCollection->except($keysToRemove);

            $manualScheduling = (bool) data_get($shift, 'manual_scheduling_only', false);
            $shiftCollection->put('manual_scheduling_only', $manualScheduling);

            return $shiftCollection;
        })
            ->sortBy(static fn($s) => $s->get('shift_date') ?? '9999-12-31')
            ->values();

        return collect([
            'shifts'       => $formattedShifts,
            'total_shifts' => count($shifts),
        ]);
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

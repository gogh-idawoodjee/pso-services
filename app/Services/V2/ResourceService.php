<?php

namespace App\Services\V2;

use App\Classes\V2\BaseService;
use App\Classes\V2\EntityBuilders\ActivityBuilder;
use App\Classes\V2\EntityBuilders\ActivityStatusBuilder;
use App\Classes\V2\EntityBuilders\ResourceEventBuilder;
use App\Classes\V2\EntityBuilders\ShiftBuilder;
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
use JsonException;
use Ramsey\Uuid\Uuid;

class ResourceService extends BaseService
{

    protected array $resources;
    protected array $rawScheduleData;
    protected array $selectOptions = [];


    public function createEvent(): JsonResponse|null
    {
        try {

            $payload =
                ResourceEventBuilder::make(data_get($this->data, 'data.resourceId'), EventType::from(data_get($this->data, 'data.eventType')))
                    ->eventDateTime(data_get($this->data, 'data.eventDateTime'))
                    ->latitude(data_get($this->data, 'data.lat'))
                    ->longitude(data_get($this->data, 'data.long'))
                    ->build();

            return $this->sendOrSimulateBuilder()
                ->payload(['Schedule_event' => $payload])
                ->environment(data_get($this->data, 'environment'))
                ->token($this->sessionToken)
                ->includeInputReference('Created Event')
                ->send();

        } catch (Exception $e) {
            $this->LogError($e, __METHOD__, __CLASS__);
            return $this->error('An unexpected error occurred', 500);
        }
    }

    public function updateShift(): JsonResponse|null
    {
        try {

            $payload = ShiftBuilder::make()
                ->shiftId(data_get($this->data, 'data.shiftId'))
                ->shiftType(data_get($this->data, 'data.shiftType'))
                ->startDateTime(data_get($this->data, 'data.startDateTime'))
                ->endDateTime(data_get($this->data, 'data.endDateTime'))
                ->arpObject(data_get($this->data, 'data.isArpObject'))
                ->description(data_get($this->data, 'data.description'))
                ->manualSchedulingOnly(data_get($this->data, 'data.isManualSchedulingOnly'))
                ->rotaId(data_get($this->data, 'data.rotaId'))
                ->resourceId(data_get($this->data, 'data.resourceId'))
                ->build();

            $entity = data_get($this->data, 'data.isArpObject') ? ShiftEntity::RAMROTAITEM->value : ShiftEntity::SHIFT->value;

            return $this->sendOrSimulate(
                [$entity => $payload],
                data_get($this->data, 'environment'),
                $this->sessionToken,
                true, // sends rota update
                'Updated Rota After Shift Update'
            );


        } catch (Exception $e) {
            $this->LogError($e, __METHOD__, __CLASS__);
            return $this->error('An unexpected error occurred', 500);
        }

    }

    public function createUnavailability(): JsonResponse|null
    {
        try {

            // starting with just schedule unavail which is just a private activity

            if (data_get($this->data, 'data.isArpObject')) {
                // to ARP first

                $payload = $this->buildArpUnavailability($this->data);

                return $this->sendOrSimulateBuilder()
                    ->payload($payload)
                    ->environment(data_get($this->data, 'environment'))
                    ->token($this->sessionToken)
                    ->includeInputReference('send unavailability to ARP')
                    ->requiresRotaUpdate(true)
                    ->send();

            }

            // straight to DSE
            $activityId = Uuid::uuid4()->toString();
            data_set($this->data, 'data.activityId', $activityId);


            $payload = ActivityBuilder::make($this->data)
                ->withActivityClass(ActivityClass::PRIVATE)
                ->withActivityStatusBuilder(
                    ActivityStatusBuilder::make($activityId, ActivityStatus::COMMITTED)
                        ->resourceId(data_get($this->data, 'data.resourceId'))
                        ->fixed(true)
                        ->dateTimeFixed(data_get($this->data, 'data.baseDateTime'))
                        ->duration(data_get($this->data, 'data.duration'))
                )
                ->build();

            return $this->sendOrSimulateBuilder()
                ->payload($payload)
                ->environment(data_get($this->data, 'environment'))
                ->token($this->sessionToken)
                ->includeInputReference('Created Unavailability')
                ->send();


        } catch (Exception $e) {
            $this->LogError($e, __METHOD__, __CLASS__);
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


    /**
     * @throws JsonException
     */
    public function getResource(string $datasetId, string $resourceId, string $baseUrl): JsonResponse
    {

        $resource = $this->getPsoData($datasetId, $baseUrl, $this->sessionToken, PsoEndpointSegment::RESOURCE, $resourceId)->getData(true);
        $resourceData = data_get($resource, 'dsScheduleData.Resources');
        $resourceTypeId = data_get($resource, 'dsScheduleData.Resources.resource_type_id');
        $resourceType = collect(data_get($resource, 'dsScheduleData.Resource_Type', []))
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
                        'full_name' => data_get($resource, 'dsScheduleData.Resources.first_name') . ' ' . data_get($resource, 'dsScheduleData.Resources.surname'),
                        'first_name' => data_get($resource, 'dsScheduleData.Resources.first_name'),
                        'surname' => data_get($resource, 'dsScheduleData.Resources.surname'),
                    ],
                    'additional_attributes' => $this->getAdditionalAttributes($resourceId, data_get($resource, 'dsScheduleData.Additional_Attribute')),
                    'resource_id' => data_get($resource, 'dsScheduleData.Resources.id'),
                    'resource_type' => [
                        'type_id' => data_get($resourceData, 'resource_type_id'),
                        'description' => data_get($resourceType, 'description'),

                    ],

                    'note' => data_get($resource, 'dsScheduleData.Resources.memo'),
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

                        ]
                    ],
                    'regions' => $this->getRelatedItemsForResource($resource, $resourceId, 'region'),
                    'skills' => $this->getRelatedItemsForResource($resource, $resourceId, 'skill'),
                    'shifts' => $this->getResourceShiftsFormatted(data_get($resource, 'dsScheduleData.Shift'), data_get($resource, 'dsScheduleData.Plan_Route'))

                ]
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
                // handle invalid format gracefully
            }
        }

        return [
            'value' => $maxTravelValue,
            'source' => $maxTravelSource,
            'formatted' => $maxTravelFormatted,
        ];

    }

    public function getRelatedItemsForResource(array $data, string|int $resourceId, $resourceEntity): array
    {

        if ($resourceEntity === 'region') {
            $relatedEntityKey = 'dsScheduleData.Resource_Region';
            $entityKey = 'region_id';
            $entityListKey = 'dsScheduleData.Region';
        }

        if ($resourceEntity === 'skill') {
            $relatedEntityKey = 'dsScheduleData.Resource_Skill';
            $entityKey = 'skill_id';
            $entityListKey = 'dsScheduleData.Skill';
        }

        $entityRelations = collect(data_get($data, $relatedEntityKey, []));
        $entityList = collect(data_get($data, $entityListKey, []));

        // Get list of region_ids for the resource
        $entityIds = $entityRelations
            ->where('resource_id', (string)$resourceId)
            ->pluck($entityKey)
            ->unique()
            ->values();

        return $entityList
            ->whereIn('id', $entityIds)
            ->map(static fn($entity) => [
                'id' => data_get($entity, 'id'),
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


    public function getResourceShiftsFormatted(array $shifts, array $routes): Collection
    {
        $groupedRoutes = collect($routes)->groupBy('shift_id');

        $formattedShifts = collect($shifts)->map(function ($shift) use ($groupedRoutes) {
            $start = Carbon::parse(data_get($shift, 'start_datetime'));
            $end = Carbon::parse(data_get($shift, 'end_datetime'));
            $shiftId = data_get($shift, 'id');

            $shiftDate = $start->toFormattedDateString();
            $shiftSpan = $start->format('H:i') . ' - ' . $end->format('H:i');
            $shiftDuration = $start->diffInHours($end);

            $routeData = $groupedRoutes->get($shiftId, collect())->first() ?? [];

            $parseInterval = static fn($interval) => $interval
                ? CarbonInterval::fromString($interval)->forHumans(['options' => CarbonInterface::FLOOR])
                : 'N/A';

            $overtimePeriod = Arr::has($shift, 'overtime_period')
                ? $parseInterval(data_get($shift, 'overtime_period'))
                : 'no overtime';

            $utilisation = [
                'percent' => data_get($routeData, 'utilisation', 0),
                'total_unutilised_time' => $parseInterval(data_get($routeData, 'total_unutilised_time')),
                'total_private_time' => $parseInterval(data_get($routeData, 'total_private_time')),
                'total_break_time' => $parseInterval(data_get($routeData, 'total_break_time')),
                'total_on_site_time' => $parseInterval(data_get($routeData, 'total_on_site_time')),
                'total_travel_time' => $parseInterval(data_get($routeData, 'total_travel_time')),
                'average_travel_time' => $parseInterval(data_get($routeData, 'average_travel_time')),
                'total_allocations' => data_get($routeData, 'total_allocations', 0),
                'route_margin' => data_get($routeData, 'route_margin', 0),
            ];

            $shiftCollection = collect($shift)
                ->put('shift_date', $shiftDate)
                ->put('shift_span', $shiftSpan)
                ->put('shift_duration', $shiftDuration)
                ->put('overtime_period', $overtimePeriod)
                ->put('utilisation', $utilisation);

            $keysToRemove = ['start_datetime', 'end_datetime', 'actual', 'split_allowed', 'resource_id'];
            $shiftCollection = $shiftCollection->except($keysToRemove);

            $manualScheduling = data_get($shift, 'manual_scheduling_only', false);
            $shiftCollection->put('manual_scheduling_only', (bool)$manualScheduling);

            return $shiftCollection;
        })->sortBy('shift_date')->values();

        return collect([
            'shifts' => $formattedShifts,
            'total_shifts' => count($shifts),
        ]);
    }


    /**
     * @throws JsonException
     */
    public function getResourceList(string $datasetId, string $baseUrl): self
    {

        $this->rawScheduleData = $this->getPsoData($datasetId, $baseUrl, $this->sessionToken, PsoEndpointSegment::DATA, null, true)->getData(true);
        $this->resources = data_get($this->rawScheduleData, 'dsScheduleData.Resources');
        return $this;

    }

    public function getResources(): array
    {
        return $this->resources;
    }

    public function getRawScheduleData(): array
    {
        return $this->rawScheduleData;
    }

    public function toResponse(): JsonResponse
    {
        return $this->ok($this->resources);
    }

    public function toSelectOptions(): self
    {
        if (!$this->resources) {
            return $this;
        }

        $selectOptions = [];

        foreach ($this->resources as $resource) {
            $id = data_get($resource, 'id');
            $firstName = data_get($resource, 'first_name', '');
            $surname = data_get($resource, 'surname', '');

            // Handle cases where surname might be missing
            $displayName = trim($firstName . ' ' . $surname);

            // If after trimming the display name is empty, use the ID or some fallback
            if (empty($displayName)) {
                $displayName = $id ?? 'Unknown Resource';
            }

            $selectOptions[$id] = $displayName;
        }

        $this->selectOptions = $selectOptions;

        return $this;
    }

    /**
     * Get the select options array
     *
     * @return array
     */
    public function getSelectOptions(): array
    {
        return $this->selectOptions ?? [];
    }


}

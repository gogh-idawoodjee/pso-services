<?php

namespace App\Classes\V2\Formatters;

use App\Classes\V2\PsoClient;
use App\Helpers\LocationHelper;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Carbon\CarbonInterval;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Throwable;

/**
 * Turns a raw GET /resource/{id} PSO response into the shaped payload
 * ResourceService::getResource() returns. Extracted so that method reads as
 * orchestration (fetch data, format it, return it) rather than a long inline transform.
 */
class ResourceFormatter
{
    /**
     * @return array|null null if the resource wasn't found in the PSO response
     */
    public static function format(array $resource, string $resourceId): array|null
    {
        $rootKey = PsoClient::resolveScheduleDataKey($resource);
        $resourceData = data_get($resource, "{$rootKey}.Resources");

        if (!$resourceData) {
            return null;
        }

        $resourceTypeId = data_get($resource, "{$rootKey}.Resources.resource_type_id");
        $resourceType = collect(data_get($resource, "{$rootKey}.Resource_Type", []))
            ->firstWhere('id', $resourceTypeId);

        return [
            'resource' => [
                'personal' => [
                    'full_name' => data_get($resource, "{$rootKey}.Resources.first_name") . ' ' . data_get($resource, "{$rootKey}.Resources.surname"),
                    'first_name' => data_get($resource, "{$rootKey}.Resources.first_name"),
                    'surname' => data_get($resource, "{$rootKey}.Resources.surname"),
                ],
                'additional_attributes' => self::additionalAttributes($resourceId, data_get($resource, "{$rootKey}.Additional_Attribute")),
                'resource_id' => data_get($resource, "{$rootKey}.Resources.id"),
                'resource_type' => [
                    'type_id' => data_get($resourceData, 'resource_type_id'),
                    'description' => data_get($resourceType, 'description'),
                ],
                'note' => data_get($resource, "{$rootKey}.Resources.memo"),
                'max_travel' => self::maxTravel($resourceData, $resourceType, 'max_travel'),
                'max_travel_outside_shift_to_first_activity' => self::maxTravel($resourceData, $resourceType, 'travel_from'),
                'max_travel_outside_shift_to_home' => self::maxTravel($resourceData, $resourceType, 'travel_to'),
                'location' => self::location($resource, $resourceData),
                'regions' => self::relatedItems($resource, $resourceId, 'region', $rootKey),
                'skills' => self::relatedItems($resource, $resourceId, 'skill', $rootKey),
                'shifts' => self::shifts(data_get($resource, "{$rootKey}.Shift"), data_get($resource, "{$rootKey}.Plan_Route")),
            ],
        ];
    }

    private static function location(array $resource, array $resourceData): array
    {
        $sameStartAndEndLocation = data_get($resourceData, 'location_id_start') === data_get($resourceData, 'location_id_end');

        $locationStart = LocationHelper::findLocationById($resource, data_get($resourceData, 'location_id_start'));
        $locationEnd = LocationHelper::findLocationById($resource, data_get($resourceData, 'location_id_end'));
        $googleLocationStart = LocationHelper::formatAddress(data_get($locationStart, 'latitude'), data_get($locationStart, 'longitude'));
        $googleLocationEnd = $sameStartAndEndLocation
            ? $googleLocationStart
            : LocationHelper::formatAddress(data_get($locationEnd, 'latitude'), data_get($locationEnd, 'longitude'));

        return [
            'same_start_and_end' => $sameStartAndEndLocation,
            'google_reverse_geocode_lookup' => [
                'start' => $googleLocationStart,
                'end' => $googleLocationEnd,
            ],
            'pso' => [
                'start' => LocationHelper::formatPsoAddress($locationStart),
                'end' => LocationHelper::formatPsoAddress($locationEnd),
            ],
        ];
    }

    private static function maxTravel(array|null $resourceData, array|null $resourceType, string $key): array
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

    private static function relatedItems(array $data, string|int $resourceId, string $resourceEntity, string $rootKey = 'dsScheduleData'): array
    {
        [$relatedEntityKey, $entityKey, $entityListKey] = match ($resourceEntity) {
            'region' => ["{$rootKey}.Resource_Region", 'region_id', "{$rootKey}.Region"],
            'skill'  => ["{$rootKey}.Resource_Skill", 'skill_id', "{$rootKey}.Skill"],
            default  => [null, null, null],
        };

        if (!$relatedEntityKey || !$entityKey || !$entityListKey) {
            return ['items' => [], 'total' => 0];
        }

        $entityRelations = collect(data_get($data, $relatedEntityKey, []));
        $entityList      = collect(data_get($data, $entityListKey, []));

        $entityIds = $entityRelations
            ->where('resource_id', (string) $resourceId)
            ->pluck($entityKey)
            ->unique()
            ->values();

        $items = $entityList
            ->whereIn('id', $entityIds)
            ->map(static fn($entity) => [
                'id'          => data_get($entity, 'id'),
                'description' => data_get($entity, 'description'),
            ])
            ->values();

        return ['items' => $items->all(), 'total' => $items->count()];
    }

    private static function additionalAttributes(string $resourceId, array|null $additionalAttributes = null): array
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

    private static function shifts(array|null $shifts, array|null $routes): Collection
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
}

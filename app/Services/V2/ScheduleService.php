<?php

namespace App\Services\V2;

use App\Classes\V2\BaseService;
use App\Classes\V2\PsoClient;
use App\Enums\PsoEndpointSegment;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use JsonException;

class ScheduleService extends BaseService
{
    /**
     * Fetch full schedule data from PSO and normalize into entity collections.
     *
     * @throws JsonException
     */
    public function getScheduleData(string $baseUrl, string $datasetId, string $token, bool $includeInput = true, bool $includeOutput = true): array|false
    {
        $response = $this->psoClient->getPsoData(
            $datasetId,
            $baseUrl,
            $token,
            PsoEndpointSegment::DATA,
            null,
            $includeInput,
            $includeOutput,
        );

        if ($response->status() !== 200) {
            return false;
        }

        $rawData = $response->getData(true);
        $rootKey = PsoClient::resolveScheduleDataKey($rawData);
        $fullSchedule = data_get($rawData, $rootKey, []);

        $activities = self::normalizeCollection($fullSchedule, 'Activity');
        $activityKeys = $activities->pluck('id');
        $activityLocations = $activities->pluck('location_id');

        return [
            'Activity' => $activities,
            'Activity_Status' => self::normalizeCollection($fullSchedule, 'Activity_Status')->whereIn('activity_id', $activityKeys)->values(),
            'Activity_SLA' => self::normalizeCollection($fullSchedule, 'Activity_SLA')->whereIn('activity_id', $activityKeys)->values(),
            'Activity_Skill' => self::normalizeCollection($fullSchedule, 'Activity_Skill')->whereIn('activity_id', $activityKeys)->values(),
            'Location' => self::normalizeCollection($fullSchedule, 'Location')->whereIn('id', $activityLocations)->values(),
            'Location_Region' => self::normalizeCollection($fullSchedule, 'Location_Region')->values(),
            'Schedule_Event' => self::normalizeCollection($fullSchedule, 'Schedule_Event')->values(),
            'Schedule_Exception_Response' => self::normalizeCollection($fullSchedule, 'Schedule_Exception_Response')->values(),
        ];
    }

    private static function normalizeCollection(array $fullSchedule, string $key): Collection
    {
        $primaryFields = [
            'Activity' => 'id',
            'Activity_Skill' => 'activity_id',
            'Activity_Status' => 'status_id',
            'Activity_SLA' => 'sla_type_id',
            'Location' => 'id',
            'Location_Region' => 'location_id',
            'Schedule_Event' => 'status_id',
            'Schedule_Exception_Response' => 'status_id',
        ];

        if (!Arr::has($fullSchedule, $key)) {
            return collect();
        }

        $section = collect($fullSchedule[$key]);

        if ($section->isEmpty()) {
            return collect();
        }

        $primaryField = $primaryFields[$key] ?? 'id';

        if ($section->has($primaryField)) {
            return collect([$section]);
        }

        return $section;
    }
}

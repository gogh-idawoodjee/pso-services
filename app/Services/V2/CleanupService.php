<?php

namespace App\Services\V2;

use App\Classes\V2\BaseService;
use App\Classes\V2\EntityBuilders\InputReferenceBuilder;
use App\Classes\V2\PsoClient;
use App\Classes\V2\PSOObjectRegistry;
use App\DataTransferObjects\PsoContext;
use App\Enums\InputMode;
use App\Enums\PsoEndpointSegment;
use App\Helpers\Stubs\DeleteObject;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;

class CleanupService extends BaseService
{
    /**
     * Deletes activities whose most recent allocation ended at or before the
     * cutoff. Always reads the live schedule and always commits the deletion
     * when anything expired is found — there is no preview/dry-run mode.
     */
    public function cleanupDataset(PsoContext $context): JsonResponse
    {
        try {
            $datasetId = $context->datasetId();
            $baseUrl = $context->baseUrl();

            $cutoff = $context->data('cutoffDatetime')
                ? Carbon::parse($context->data('cutoffDatetime'))
                : Carbon::now()->setTimezone(config('pso-services.defaults.timezone', 'America/Toronto'))->startOfDay();

            $scheduleResponse = $this->psoClient->getPsoData(
                $datasetId,
                $baseUrl,
                $context->token,
                PsoEndpointSegment::DATA,
                includeOutput: true,
            );

            if ($scheduleResponse->status() !== 200) {
                return $scheduleResponse;
            }

            $expiredActivityIds = $this->findExpiredActivityIds($scheduleResponse->getData(true), $cutoff);

            if ($expiredActivityIds->isEmpty()) {
                return $this->ok(
                    ['cleanupSummary' => ['activitiesDeleted' => 0, 'activityIds' => []]],
                    'Nothing to clean up.',
                );
            }

            $registry = PSOObjectRegistry::resolveEntry('Activity');

            $objectDeletions = $expiredActivityIds
                ->map(static fn ($activityId) => DeleteObject::make($registry, ['objectPk1' => $activityId]))
                ->values()
                ->all();

            $payload = [
                'Object_Deletion' => $objectDeletions,
                'Input_Reference' => InputReferenceBuilder::make($datasetId)
                    ->inputType(InputMode::CHANGE)
                    ->description('Dataset Cleanup: '.count($objectDeletions).' expired activity(ies)')
                    ->build(),
            ];

            $psoPayload = $this->psoClient->buildPayload($payload, $context->psoApiVersion());
            $psoResponse = $this->psoClient->sendToPso($psoPayload, $context->environment(), $context->token, PsoEndpointSegment::DATA);

            if ($psoResponse->status() >= 400) {
                return $psoResponse;
            }

            return $this->sentToPso([
                'cleanupSummary' => [
                    'activitiesDeleted' => $expiredActivityIds->count(),
                    'activityIds' => $expiredActivityIds->all(),
                ],
                'responseFromPso' => $psoResponse->getData(),
            ]);
        } catch (Exception $e) {
            $this->logError($e, __METHOD__, __CLASS__);

            return $this->error('An unexpected error occurred', 500);
        }
    }

    /** @return Collection<int, string> */
    private function findExpiredActivityIds(array $rawData, Carbon $cutoff): Collection
    {
        $rootKey = PsoClient::resolveScheduleDataKey($rawData);
        $allocations = collect(data_get($rawData, "{$rootKey}.Allocation", []));

        // PSO returns a bare object instead of a list when there's exactly one allocation
        if ($allocations->has('activity_id')) {
            $allocations = collect([$allocations]);
        }

        return $allocations
            ->filter(static function ($allocation) use ($cutoff) {
                $activityEnd = data_get($allocation, 'activity_end');

                return $activityEnd && Carbon::parse($activityEnd)->lte($cutoff);
            })
            ->pluck('activity_id')
            ->unique()
            ->values();
    }
}

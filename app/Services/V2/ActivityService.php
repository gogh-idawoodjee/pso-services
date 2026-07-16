<?php

namespace App\Services\V2;

use App\Classes\V2\BaseService;
use App\Classes\V2\EntityBuilders\ActivityStatusBuilder;
use App\Classes\V2\PSOObjectRegistry;
use App\DataTransferObjects\PsoContext;
use App\Enums\ActivityStatus;
use App\Helpers\Stubs\DeleteObject;
use Exception;
use Illuminate\Http\JsonResponse;

class ActivityService extends BaseService
{
    public function updateStatus(PsoContext $context, ActivityStatus $activityStatus, string|null $resourceId = null): JsonResponse
    {
        try {
            $payload = ActivityStatusBuilder::make($context->data('activityId'), $activityStatus)
                ->resourceId($resourceId)
                ->duration($context->data('duration'))
                ->fixed((bool) $resourceId)
                ->build();

            return $this->psoClient->sendOrSimulateBuilder()
                ->payload(['Activity_Status' => $payload])
                ->environment($context->environment())
                ->psoApiVersion($context->psoApiVersion())
                ->token($context->token)
                ->includeInputReference()
                ->send();
        } catch (Exception $e) {
            $this->logError($e, __METHOD__, __CLASS__);
            return $this->error('An unexpected error occurred', 500);
        }
    }

    public function deleteActivities(PsoContext $context): JsonResponse
    {
        try {
            $activitiesList = $context->data('activities');
            $registry = PSOObjectRegistry::resolveEntry('activity');

            $payload = [
                'Object_Deletion' => collect($activitiesList)->map(static fn($id) => DeleteObject::make(
                    $registry,
                    ['objectPk1' => $id],
                ))->all(),
            ];

            return $this->psoClient->sendOrSimulateBuilder()
                ->payload($payload)
                ->environment($context->environment())
                ->psoApiVersion($context->psoApiVersion())
                ->token($context->token)
                ->includeInputReference('Delete Activities: ' . implode(', ', $activitiesList))
                ->send();
        } catch (Exception $e) {
            $this->logError($e, __METHOD__, __CLASS__);
            return $this->error('An unexpected error occurred', 500);
        }
    }
}

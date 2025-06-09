<?php

namespace App\Services\V2;

use App\Classes\V2\BaseService;
use App\Classes\V2\EntityBuilders\ActivityStatusBuilder;
use App\Enums\ActivityStatus;
use App\Helpers\Stubs\DeleteObject;
use Exception;
use Illuminate\Http\JsonResponse;
use SensitiveParameter;

// careful your two ActivityStatus classes don't clash

class ActivityService extends BaseService
{
    private string|null $activityId;
    private string|null $resourceId;
    private ActivityStatus|null $activityStatus;

    public function __construct(
        #[SensitiveParameter] string|null $sessionToken = null,
                                          $data,
        string|null                       $activityId = null,
        ActivityStatus|null               $activityStatus = null,
        string|null                       $resourceId = null
    )
    {
        parent::__construct($sessionToken, $data);
        $this->activityId = $activityId;
        $this->activityStatus = $activityStatus;
        $this->resourceId = $resourceId;
    }

    public function updateStatus(): JsonResponse
    {
        try {


            $payload = ActivityStatusBuilder::make($this->activityId, $this->activityStatus)
                ->resourceId($this->resourceId)
                ->duration(data_get($this->data, 'data.duration'))
                ->fixed((bool)$this->resourceId)
                ->build();

            return $this->sendOrSimulateBuilder()
                ->payload(['Activity_Status' => $payload])
                ->environment(data_get($this->data, 'environment'))
                ->token($this->sessionToken)
                ->includeInputReference()
                ->send();

        } catch (Exception $e) {
            $this->LogError($e, __METHOD__, __CLASS__);
            return $this->error('An unexpected error occurred', 500);
        }
    }

    public function deleteActivities(): JsonResponse
    {
        try {
            $activitiesList = data_get($this->data, 'data.activities');

            $payload = [
                'Object_Deletion' => collect($activitiesList)->map(static fn($id) => DeleteObject::make([
                    'objectType' => 'activity',
                    'objectPk1' => $id,
                ]))->all(),
            ];


            return $this->sendOrSimulateBuilder()
                ->payload($payload)
                ->environment(data_get($this->data, 'environment'))
                ->token($this->sessionToken)
                ->includeInputReference()
                ->send();

        } catch (Exception $e) {
            $this->LogError($e, __METHOD__, __CLASS__);
            return $this->error('An unexpected error occurred', 500);
        }
    }

}

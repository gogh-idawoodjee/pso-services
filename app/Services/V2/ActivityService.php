<?php

namespace App\Services\V2;

use App\Classes\V2\BaseService;
use App\Enums\ActivityStatus;
use App\Helpers\Stubs\ActivityStatus as StubActivityStatus;
use App\Helpers\Stubs\DeleteObject;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
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
            $payload = StubActivityStatus::make(
                $this->activityId,
                $this->activityStatus,
                $this->resourceId,
                (bool)$this->resourceId
            );

            return $this->sendOrSimulate(
                ['Activity_Status' => $payload],
                data_get($this->data, 'environment'),
                $this->sessionToken
            );
        } catch (Exception $e) {
            Log::error('Unexpected error in updateStatus: ' . $e->getMessage());
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

            return $this->sendOrSimulate(
                $payload,
                data_get($this->data, 'environment'),
                $this->sessionToken
            );
        } catch (Exception $e) {
            Log::error('Unexpected error in deleteActivities: ' . $e->getMessage());
            return $this->error('An unexpected error occurred', 500);
        }
    }

}

<?php

namespace App\Services\V2;

use App\Classes\V2\BaseService;
use App\Enums\ActivityStatus;
use App\Helpers\Stubs\ActivityStatus as StubActivityStatus;
use Illuminate\Http\JsonResponse;
use SensitiveParameter;

// careful your two ActivityStatus classes don't clash

class ActivityService extends BaseService
{
    private string $activityId;
    private string|null $resourceId;
    private ActivityStatus $activityStatus;


    public function __construct(#[SensitiveParameter] string|null $sessionToken = null, string $activityId, ActivityStatus $activityStatus, string|null $resourceId = null)
    {

        parent::__construct($sessionToken);

        $this->activityId = $activityId;
        $this->activityStatus = $activityStatus;
        $this->resourceId = $resourceId;

    }

    public function updateStatus(): JsonResponse
    {
        // create the payload
        $payload = StubActivityStatus::make($this->activityId, $this->activityStatus, $this->resourceId, (bool)$this->resourceId);
        // send if needed to send
        if ($this->sessionToken) {
            // call sendToPso method
        }
        return $this->notSentToPso(($this->buildPayload(['Activity_Status' => $payload], 1, true)));

    }
}

<?php

namespace App\Services\V2;

use App\Classes\V2\BaseService;
use App\Enums\ActivityStatus;
use App\Enums\PsoEndpointSegment;
use App\Helpers\Stubs\ActivityStatus as StubActivityStatus;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use SensitiveParameter;

// careful your two ActivityStatus classes don't clash

class ActivityService extends BaseService
{
    private string $activityId;
    private string|null $resourceId;
    private ActivityStatus $activityStatus;


    public function __construct(#[SensitiveParameter] string|null $sessionToken = null, $data, string $activityId, ActivityStatus $activityStatus, string|null $resourceId = null)
    {

        parent::__construct($sessionToken, $data);

        $this->activityId = $activityId;
        $this->activityStatus = $activityStatus;
        $this->resourceId = $resourceId;

    }

    public function updateStatus(): JsonResponse
    {

        try {
            $payload = StubActivityStatus::make($this->activityId, $this->activityStatus, $this->resourceId, (bool)$this->resourceId);
            $environmentData = data_get($this->data, 'environment');


            if ($this->sessionToken) {
                $psoPayload = $this->buildPayload($payload);

                $psoResponse = $this->sendToPso(
                    $psoPayload,
                    $environmentData,
                    $this->sessionToken,
                    PsoEndpointSegment::DATA
                );

                // Check if response is successful (status code < 400)
                if ($psoResponse->status() < 400) {

                    return $this->ok($psoResponse->getData());
                }

                // If there was an error, just return the error response
                return $psoResponse;
            }

            return $this->notSentToPso($this->buildPayload($payload, 1, true));
        } catch (Exception $e) {
            Log::error('Unexpected error in getAppointment: ' . $e->getMessage());
            return $this->error('An unexpected error occurred', 500);
        }


    }
}

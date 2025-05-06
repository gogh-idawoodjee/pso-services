<?php

namespace App\Services\V2;

use App\Classes\V2\BaseService;
use App\Enums\ShiftEntity;
use App\Helpers\Stubs\ResourceEvent;
use App\Helpers\Stubs\Shift;
use Exception;
use Illuminate\Http\JsonResponse;
use SensitiveParameter;

class ResourceService extends BaseService
{

    public function __construct(#[SensitiveParameter] string|null $sessionToken = null, $data)
    {
        parent::__construct($sessionToken, $data);
    }

    public function createEvent(): JsonResponse|null
    {
        try {
            $payload = ResourceEvent::make(
                data_get($this->data, 'resourceId'),
                data_get($this->data, 'eventType'),
                data_get($this->data, 'eventDateTime'),
                data_get($this->data, 'lat'),
                data_get($this->data, 'long'),
            );

            return $this->sendOrSimulate(
                ['Schedule_Event' => $payload],
                data_get($this->data, 'environment'),
                $this->sessionToken
            );
        } catch (Exception $e) {
            $this->LogError($e, __METHOD__, __CLASS__);
            return $this->error('An unexpected error occurred', 500);
        }
    }

    public function updateShift(): JsonResponse|null
    {
        try {
            $payload = Shift::make(
                data_get($this->data, 'data.shiftId'),
                data_get($this->data, 'data.resourceId'),
                data_get($this->data, 'data.rotaId'),
                data_get($this->data, 'data.startDateTime'),
                data_get($this->data, 'data.endDateTime'),
                data_get($this->data, 'data.isManualSchedulingOnly'),
                data_get($this->data, 'data.shiftType'),
                data_get($this->data, 'data.description'),
                data_get($this->data, 'data.isArpShift'),
            );

            $entity = data_get($this->data, 'data.isArpShift') ? ShiftEntity::RAMROTAITEM->value : ShiftEntity::SHIFT->value;

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
}

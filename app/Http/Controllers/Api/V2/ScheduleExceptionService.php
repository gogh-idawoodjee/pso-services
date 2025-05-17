<?php

namespace App\Http\Controllers\Api\V2;

use App\Classes\V2\BaseService;
use App\Helpers\Stubs\CustomException;
use App\Helpers\Stubs\CustomExceptionData;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class ScheduleExceptionService extends BaseService
{

    public function createException(): JsonResponse
    {

        try {

            $entityIsActivity = false;

            if (data_get($this->data, 'data.activityId')) {
                $entityIsActivity = true;
                $entityId = data_get($this->data, 'data.activityId');
            } else {
                $entityId = data_get($this->data, 'data.resourceId');
            }

            $customExceptionId = Str::orderedUuid()->getHex()->toString();

            $customException = CustomException::make(
                $customExceptionId,
                data_get($this->data, 'data.exceptionTypeId'),
                $entityId,
                $entityIsActivity
            );

            $customExceptionData = CustomExceptionData::make(
                $customExceptionId,
                data_get($this->data, 'data.label'),
                data_get($this->data, 'data.value'),
            );

            return $this->sendOrSimulateBuilder()
                ->payload(['Custom_Exception' => $customException, 'Custom_Exception_Data' => $customExceptionData])
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

<?php

namespace App\Services\V2;

use App\Classes\V2\BaseService;
use App\DataTransferObjects\PsoContext;
use App\Helpers\Stubs\CustomException;
use App\Helpers\Stubs\CustomExceptionData;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class ScheduleExceptionService extends BaseService
{
    public function createException(PsoContext $context): JsonResponse
    {
        try {
            $entityIsActivity = false;

            if ($context->data('activityId')) {
                $entityIsActivity = true;
                $entityId = $context->data('activityId');
            } else {
                $entityId = $context->data('resourceId');
            }

            $customExceptionId = Str::orderedUuid()->getHex()->toString();

            $customException = CustomException::make(
                $customExceptionId,
                $context->data('exceptionTypeId'),
                $entityId,
                $entityIsActivity,
            );

            $customExceptionData = CustomExceptionData::make(
                $customExceptionId,
                $context->data('label'),
                $context->data('value'),
            );

            return $this->psoClient->sendOrSimulateBuilder()
                ->payload(['Custom_Exception' => $customException, 'Custom_Exception_Data' => $customExceptionData])
                ->environment($context->environment())
                ->token($context->token)
                ->includeInputReference()
                ->send();
        } catch (Exception $e) {
            $this->logError($e, __METHOD__, __CLASS__);
            return $this->error('An unexpected error occurred', 500);
        }
    }
}

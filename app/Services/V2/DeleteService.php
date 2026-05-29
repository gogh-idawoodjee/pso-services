<?php

namespace App\Services\V2;

use App\Classes\V2\BaseService;
use App\Classes\V2\PSOObjectRegistry;
use App\DataTransferObjects\PsoContext;
use App\Helpers\Stubs\DeleteObject;
use Exception;
use Illuminate\Http\JsonResponse;

class DeleteService extends BaseService
{
    public function deleteObject(PsoContext $context, bool $isRotaObject = false): JsonResponse
    {
        try {
            $data = $context->data();
            $objectType = $data['objectType'] ?? null;

            if (!$objectType) {
                $this->logError('Object type is missing from request data.', __METHOD__, __CLASS__);
                return $this->error('Object type is required', 422);
            }

            $key = PSOObjectRegistry::resolveKey($objectType);

            if (!$key) {
                $this->logError("Object type '{$objectType}' not found in registry.", __METHOD__, __CLASS__);
                return $this->error("Invalid object type '{$objectType}'", 422);
            }

            $registry = PSOObjectRegistry::get($key);
            if (!$registry) {
                $this->logError("Registry entry for key '{$key}' not found.", __METHOD__, __CLASS__);
                return $this->error("Invalid object type '{$objectType}'", 422);
            }

            $label = $registry['label'] ?? $objectType;
            $data['objectType'] = $label;

            $delete_input = DeleteObject::make($data, $isRotaObject);

            return $this->psoClient->sendOrSimulateBuilder()
                ->payload(['Object_Deletion' => $delete_input])
                ->environment($context->environment())
                ->psoApiVersion($context->psoApiVersion())
                ->includeInputReference('Delete Object: ' . $label)
                ->token($context->token)
                ->send();
        } catch (Exception $e) {
            $this->logError($e, __METHOD__, __CLASS__);
            return $this->error('An unexpected error occurred', 500);
        }
    }
}

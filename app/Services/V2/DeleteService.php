<?php

namespace App\Services\V2;

use App\Classes\V2\BaseService;
use App\Classes\V2\PSOObjectRegistry;
use App\Helpers\Stubs\DeleteObject;
use Exception;
use Illuminate\Http\JsonResponse;
use SensitiveParameter;

class DeleteService extends BaseService
{
    protected array $data;
    protected bool $isRotaObject;

    public function __construct(#[SensitiveParameter] string|null $sessionToken = null, array $data, bool $isRotaObject = false)
    {

        parent::__construct($sessionToken, $data);
        $this->data = $data;
        $this->isRotaObject = $isRotaObject;

    }

    public function deleteObject(): JsonResponse
    {
        try {
            $input = data_get($this->data, 'data');
            $objectType = $input['objectType'] ?? null;

            if (!$objectType) {
                $this->LogError('Object type is missing from request data.', __METHOD__, __CLASS__);
                return $this->error('Object type is required', 422);
            }

            // Resolve the registry key for the provided objectType (label or entity)
            $key = PSOObjectRegistry::resolveKey($objectType);

            if (!$key) {
                $this->LogError("Object type '{$objectType}' not found in registry.", __METHOD__, __CLASS__);
                return $this->error("Invalid object type '{$objectType}'", 422);
            }

            $registry = PSOObjectRegistry::get($key);
            if (!$registry) {
                $this->LogError("Registry entry for key '{$key}' not found.", __METHOD__, __CLASS__);
                return $this->error("Invalid object type '{$objectType}'", 422);
            }

            // Use the normalized label (expected by DeleteObject::make)
            $label = $registry['label'] ?? $objectType;

            // Replace objectType in payload with normalized label
            $this->data['data'] = array_merge($this->data['data'], ['objectType' => $label]);


            // Now call the DeleteObject helper with normalized data
            $delete_input = DeleteObject::make($this->data['data'], $this->isRotaObject);

            return $this->sendOrSimulateBuilder()
                ->payload(['Object_Deletion' => $delete_input])
                ->environment(data_get($this->data, 'environment'))
                ->includeInputReference()
                ->token($this->sessionToken)
                ->send();

        } catch (Exception $e) {
            $this->LogError($e, __METHOD__, __CLASS__);
            return $this->error('An unexpected error occurred', 500);
        }
    }
}

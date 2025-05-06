<?php

namespace App\Services\V2;

use App\Classes\V2\BaseService;
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
            $payload = DeleteObject::make($this->data, $this->isRotaObject);

            return $this->sendOrSimulate(
                ['Object_Deletion' => $payload],
                data_get($this->data, 'environment'),
                $this->sessionToken
            );

        } catch (Exception $e) {
            $this->LogError($e, __METHOD__, __CLASS__);
            return $this->error('An unexpected error occurred', 500);
        }


    }
}

<?php

namespace App\Services\V2;

use App\Classes\V2\BaseService;
use App\Helpers\Stubs\DeleteObject;
use Illuminate\Http\JsonResponse;
use SensitiveParameter;

class DeleteService extends BaseService
{
    protected array $data;
    protected bool $isRotaObject;

    public function __construct(#[SensitiveParameter] string|null $sessionToken = null, array $data, bool $isRotaObject = false)
    {

        parent::__construct($sessionToken);
        $this->data = $data;
        $this->isRotaObject = $isRotaObject;

    }

    public function deleteObject(): JsonResponse
    {

        $payload = DeleteObject::make($this->data, $this->isRotaObject);

        if ($this->sessionToken) {
            // call sendToPso method
        }
        return $this->notSentToPso(($this->buildPayload(['Object_Deletion' => $payload], 1, true)));

    }
}

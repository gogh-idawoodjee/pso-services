<?php

namespace App\Services\V2;

use App\Classes\V2\BaseService;
use App\Classes\V2\EntityBuilders\InputReferenceBuilder;
use App\Enums\InputMode;
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
            $delete_input = DeleteObject::make(data_get($this->data, 'data'), $this->isRotaObject);


            $input_ref = InputReferenceBuilder::make(data_get($this->data, 'environment.datasetId'))
                ->inputType(InputMode::CHANGE)
                ->build();

            $payload = ['Object_Deletion' => $delete_input, 'Input_Reference' => $input_ref];

            return $this->sendOrSimulateBuilder()
                ->payload(['Object_Deletion' => $payload])
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

<?php

namespace App\Services\V2;

use App\Classes\BaseService;

use App\Helpers\Stubs\AppointmentRequest;
use Illuminate\Http\JsonResponse;
use SensitiveParameter;

class AppointmentService extends BaseService
{
    protected array $data;


    public function __construct(#[SensitiveParameter] string|null $sessionToken = null, array $data)
    {

        parent::__construct($sessionToken);
        $this->data = $data;


    }

    public function getAppointment(): JsonResponse
    {

        $payload = AppointmentRequest::make($this->data);

        if ($this->sessionToken) {
            // call sendToPso method
        }
        return $this->notSentToPso($this->buildPayload($payload, 1, true));

    }
}

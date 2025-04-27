<?php

namespace App\Classes;


use App\Traits\V2\PSOAssistV2;
use SensitiveParameter;

abstract class BaseService
{

    use PSOAssistV2;

    protected string|null $sessionToken;


    public function __construct(#[SensitiveParameter] string|null $sessionToken = null)
    {
        $this->sessionToken = $sessionToken;
    }

    public function sendPayloadToPso(array $payload)
    {


    }

}

<?php

namespace App\Services\V2;

class PayloadService
{

    private int $jsonVersion;

    public function __construct(int $jsonVersion = 1)
    {
        $this->jsonVersion = $jsonVersion;
    }

    public function basePayload()
    {

    }

}

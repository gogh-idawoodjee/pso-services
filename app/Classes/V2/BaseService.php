<?php

namespace App\Classes\V2;


use App\Traits\V2\PSOAssistV2;
use Exception;
use Illuminate\Support\Facades\Log;
use SensitiveParameter;

abstract class BaseService
{

    use PSOAssistV2;

    protected string|null $sessionToken;
    protected array $data;


    public function __construct(#[SensitiveParameter] string|null $sessionToken = null, array $data)
    {
        $this->sessionToken = $sessionToken;
        $this->data = $data;
    }

    public function LogError(Exception $e, $method, $class): void
    {
        Log::error('Unexpected error in ' . $method . '. This method is inside ' . $class . ': ' . $e->getMessage());
    }

}

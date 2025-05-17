<?php

namespace App\Helpers\Stubs;

class CustomExceptionData
{
    public static function make(
        string $exceptionId,
        string $label,
        string $value,
        int    $psoApiVersion = 1
    ): array
    {
        return [

            'custom_exception_id' => $exceptionId,
            'label' => $label,
            'sequence' => 1,
            'value' => $value

        ];
    }
}

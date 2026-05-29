<?php

namespace App\Helpers\Stubs;

class CustomException
{

    public static function make(
        string $exceptionId,
        int    $exceptionTypeId,
        string $entityId,
        bool   $entityIsActivity = false,
    ): array
    {

        $key = $entityIsActivity ? 'activity_id' : 'resource_id';

        return [
            'id' => $exceptionId,
            'schedule_exception_type_id' => $exceptionTypeId,
            $key => $entityId,
        ];

    }
}

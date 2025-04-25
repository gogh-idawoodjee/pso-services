<?php

namespace App\Enums;


enum ProcessType: string
{
    case DYNAMIC = 'DYNAMIC';
    case APPOINTMENT = 'APPOINTMENT';
    case REACTIVE = 'REACTIVE';
    case STATIC = 'STATIC';

    public function getLabel(): string|null
    {

        return match ($this) {
            self::DYNAMIC => 'Dynamic',
            self::APPOINTMENT => 'Appointment',
            self::REACTIVE => 'Reactive',
            self::STATIC => 'Static',

        };

    }
}

<?php

namespace App\Enums;

enum InputMode: string
{
    case LOAD = 'LOAD';
    case CHANGE = 'CHANGE';

    public function label(): string
    {
        return match ($this) {
            self::LOAD => 'Load',
            self::CHANGE => 'Change',
        };
    }
}

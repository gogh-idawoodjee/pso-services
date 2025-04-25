<?php

namespace App\Enums;

enum InputMode: string
{
    case LOAD = 'LOAD';
    case CHANGE = 'CHANGE';

    public function getLabel(): string|null
    {
        return match ($this) {
            self::LOAD => 'Load',
            self::CHANGE => 'Change'

        };

    }

}

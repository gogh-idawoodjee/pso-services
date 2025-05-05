<?php

namespace App\Enums;

enum ShiftEntity: string
{
    case SHIFT = 'Shift';
    case RAMROTAITEM = 'RAM_Rota_item';

    public function getLabel(): string|null
    {
        return match ($this) {
            self::SHIFT => 'Shift',
            self::RAMROTAITEM => 'Ram_Rota_item'

        };

    }

}

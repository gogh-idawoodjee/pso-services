<?php

namespace App\Enums;

enum ShiftEntity: string
{
    case SHIFT = 'Shift';
    case RAMROTAITEM = 'RAM_Rota_Item';

    public function label(): string
    {
        return match ($this) {
            self::SHIFT => 'Shift',
            self::RAMROTAITEM => 'RAM Rota Item',
        };
    }
}

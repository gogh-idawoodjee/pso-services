<?php

namespace App\Enums;

enum AppointmentRequestStatus: int
{

    case UNACKNOWLEDGED = 0;
    case ACCEPTED = 1;
    case DECLINED = 2;
    case CHECKED = 3;
    case FAILED = -1;

    public function label(): string
    {
        return match ($this) {
            self::UNACKNOWLEDGED => 'UNACKNOWLEDGED',
            self::ACCEPTED => 'ACCEPTED',
            self::DECLINED => 'DECLINED',
            self::CHECKED => 'CHECKED',
            self::FAILED => 'FAILED',
        };
    }

    public function isAcceptedOrDeclined(): bool
    {
        return $this === self::ACCEPTED || $this === self::DECLINED;
    }

}

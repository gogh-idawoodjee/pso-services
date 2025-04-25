<?php

namespace App\Enums;

enum BroadcastAllocationType: int
{
    case DYNAMIC_SCHEDULING = 1;
    case APPOINTMENT_BOOKING_ENGINE = 2;
    case MANUAL_SCHEDULING = 4;
    case SCHEDULE_DISPATCH_SERVICE = 8;
    case SCHEDULING_TRAVEL_ANALYSER = 16;

    public function label(): string
    {
        return match ($this) {
            self::DYNAMIC_SCHEDULING => 'Dynamic Scheduling',
            self::APPOINTMENT_BOOKING_ENGINE => 'Appointment Booking Engine',
            self::MANUAL_SCHEDULING => 'Manual Scheduling',
            self::SCHEDULE_DISPATCH_SERVICE => 'Schedule Dispatch Service',
            self::SCHEDULING_TRAVEL_ANALYSER => 'Scheduling Travel Analyser',
        };
    }
}

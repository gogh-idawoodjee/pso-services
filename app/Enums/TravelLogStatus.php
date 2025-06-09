<?php

namespace App\Enums;

enum TravelLogStatus: string
{
    case CREATED = 'created';
    case SENT = 'sent';
    case COMPLETED = 'completed';
    case FAILED = 'failed';
    case TIMEOUT = 'timed out';

    public function message(): string
    {
        return match ($this) {
            self::CREATED => 'The travel log has been created.',
            self::SENT => 'The travel log has been sent but is awaiting a response from PSO.',
            self::COMPLETED => 'The travel log process is completed.',
            self::FAILED => 'The travel log process failed.',
            self::TIMEOUT => 'The travel log process has timed out. Please check PSO Broadcast and Event Logs.',
        };
    }
}

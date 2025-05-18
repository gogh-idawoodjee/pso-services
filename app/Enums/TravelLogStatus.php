<?php

namespace App\Enums;

enum TravelLogStatus: string
{
    case CREATED = 'created';
    case SENT = 'sent';
    case COMPLETED = 'completed';
    case RECEIVED = 'received';
    case FAILED = 'failed';

    public function message(): string
    {
        return match($this) {
            self::CREATED => 'The travel log has been created.',
            self::SENT => 'The travel log has been sent but is awaiting a response from PSO.',
            self::COMPLETED => 'The travel log process is completed.',
            self::RECEIVED => 'The travel log response has been received.',
            self::FAILED => 'The travel log process failed.',
        };
    }
}

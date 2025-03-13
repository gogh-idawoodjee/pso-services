<?php

namespace App\Enums;

enum ActivityStatus: string
{

    case IGNORE = '-1';
    case UNALLOCATED = '0';
    case ALLOCATED = '10';
    case COMMITTED = '30';
    case SENT = '32';
    case DOWNLOADED = '35';
    case ACCEPTED = '40';
    case TRAVELLING = '50';
    case WAITING = '55';
    case ONSITE = '60';
    case PENDINGCOMPLETION = '65';
    case VISITCOMPLETE = '68';
    case COMPLETED = '70';
    case INCOMPLETE = '80';

    public function label(): string
    {
        return match ($this) {
            ActivityStatus::IGNORE => 'Ignore',
            ActivityStatus::UNALLOCATED => 'Unallocated',
            ActivityStatus::ALLOCATED => 'Allocated',
            ActivityStatus::COMMITTED => 'Committed',
            ActivityStatus::SENT => 'Sent',
            ActivityStatus::DOWNLOADED => 'Downloaded',
            ActivityStatus::ACCEPTED => 'Accepted',
            ActivityStatus::TRAVELLING => 'Travelling',
            ActivityStatus::WAITING => 'Wiating',
            ActivityStatus::ONSITE => 'Onsite',
            ActivityStatus::PENDINGCOMPLETION => 'Pending Completion',
            ActivityStatus::VISITCOMPLETE => 'Visit Complete',
            ActivityStatus::INCOMPLETE => 'Incomplete',
            ActivityStatus::COMPLETED => 'Completed',
        };
    }

}

<?php

namespace App\Enums;

use ValueError;

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
            self::IGNORE => 'Ignore',
            self::UNALLOCATED => 'Unallocated',
            self::ALLOCATED => 'Allocated',
            self::COMMITTED => 'Committed',
            self::SENT => 'Sent',
            self::DOWNLOADED => 'Downloaded',
            self::ACCEPTED => 'Accepted',
            self::TRAVELLING => 'Travelling',
            self::WAITING => 'Waiting',
            self::ONSITE => 'Onsite',
            self::PENDINGCOMPLETION => 'Pending Completion',
            self::VISITCOMPLETE => 'Visit Complete',
            self::INCOMPLETE => 'Incomplete',
            self::COMPLETED => 'Completed',
        };
    }

    public static function allStatuses(): array
    {
        static $cache = null;

        if ($cache === null) {
            $cache = collect(self::cases())
                ->filter(static fn(self $status) => (int)$status->value >= 0)
                ->mapWithKeys(static fn(self $status) => [
                    strtolower($status->name) => (int)$status->value,
                ])
                ->toArray();
        }

        return $cache;
    }

    public static function statusesGreaterThanAllocated(): array
    {
        static $cache = null;

        if ($cache === null) {
            $cache = collect(self::cases())
                ->filter(static fn(self $status) => (int)$status->value >= 10) // todo make this 10 value a parameter
                ->mapWithKeys(static fn(self $status) => [
                    strtolower($status->name) => (int)$status->value,
                ])
                ->toArray();
        }

        return $cache;
    }

    public static function fromNameOrFail(string $name): self
    {
        $name = strtolower($name);

        foreach (self::cases() as $case) {
            if (strtolower($case->name) === $name) {
                return $case;
            }
        }

        throw new ValueError("Invalid ActivityStatus: {$name}");
    }

}

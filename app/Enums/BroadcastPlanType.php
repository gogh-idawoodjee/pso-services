<?php

namespace App\Enums;

enum BroadcastPlanType: string
{
    case CHANGE = 'CHANGE';
    case COMPLETE = 'COMPLETE';
    case INTERNAL = 'INTERNAL';
    case ADMIN = 'ADMIN';
    case WORKBENCH = 'WORKBENCH';

    public function description(): string
    {
        return match ($this) {
            self::CHANGE => 'Only changes in the last plan.',
            self::COMPLETE => 'Whole plan sent.',
            self::INTERNAL => 'Used to inform the DSE of when to write plans internally, where no external plans are required.',
            self::ADMIN => 'Broadcasts for internal administrative purposes. Only used by the Scheduling Administration Service.',
            self::WORKBENCH => 'Broadcasts manual change requests to a third party rather than sending them to the Schedule Input Manager. The Schedule Broadcast Manager is not required. This can be overridden for accepting schedule exceptions by setting the StandardSendScheduleExceptionAccepts parameter to yes.',
        };
    }
}

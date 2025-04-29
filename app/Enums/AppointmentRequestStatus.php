<?php

namespace App\Enums;

enum AppointmentRequestStatus: int
{

    case UNACKNOWLEDGED = 0;
    case ACCEPTED = 1;
    case DECLINED = 2;
    case CHECKED = 3;
    case FAILED = -1;

}

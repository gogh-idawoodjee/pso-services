<?php

namespace App\Enums;

enum PsoEndpointSegment: string
{

    case APPOINTMENT = 'appointment';
    case SESSION = 'session';
    case DATA = 'data';

    case RESOURCE = 'resource';

}

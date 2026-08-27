<?php

namespace App\Enums;

enum RamUpdateType: string
{
    case LOAD = 'LOAD';
    case CHANGE = 'CHANGE';
    case DELETE = 'DELETE';
}

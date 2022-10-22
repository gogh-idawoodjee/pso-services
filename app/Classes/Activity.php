<?php

namespace App\Classes;


use Illuminate\Support\Collection;
use stdClass;

abstract class Activity
{

    protected string $activity_id;

    public function __construct()
    {

    }

    public function getActivityID()
    {
        return $this->activity_id;
    }
}

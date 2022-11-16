<?php

namespace App\Classes;


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

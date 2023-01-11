<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PSOAppointment extends Model
{
    use HasFactory;
    use Uuids;

    protected $table = 'appointment_request';
    protected $guarded = [];


}

<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PSOAppointment extends Model
{
    use HasFactory;
    use Uuids;

    // todo think about encrypting input_request because it could contain passwords

    protected $table = 'appointment_request';
    protected $guarded = [];
    protected $dates = ['accept_decline_datetime', 'appointment_template_datetime', 'offer_expiry_datetime', 'appointed_check_datetime'];
    protected $casts = [
        'appointed_check_complete' => 'boolean',
        'appointed_check_result' => 'boolean'
    ];


}

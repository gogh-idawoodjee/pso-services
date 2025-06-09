<?php

namespace App\Models\V2;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static Builder|static where(string $column, mixed $operator = null, mixed $value = null)
 * @method static Builder|static query()
 * @method static Model|static create(array $attributes = [])
 */
class PSOAppointment extends Model
{

    use Uuids;


    protected $table = 'appointment_request';
    protected $guarded = [];
//    protected $dates = ['accept_decline_datetime', 'appointment_template_datetime', 'offer_expiry_datetime', 'appointed_check_datetime'];
    protected $casts = [
        'appointed_check_complete' => 'boolean',
        'appointed_check_result' => 'boolean',
        'accept_decline_datetime' => 'datetime',
        'appointment_template_datetime' => 'datetime',
        'offer_expiry_datetime' => 'datetime',
        'appointed_check_datetime' => 'datetime'
    ];

    protected function inputRequest(): Attribute
    {
        return Attribute::make(
            get: static fn(string $value) => json_decode($value, false, 512, JSON_THROW_ON_ERROR),
        );
    }

    protected function appointmentRequest(): Attribute
    {
        return Attribute::make(
            get: static fn(string $value) => json_decode($value, false, 512, JSON_THROW_ON_ERROR),
        );
    }

    protected function appointmentResponse(): Attribute
    {
        return Attribute::make(
            get: static fn(string $value) => json_decode($value, false, 512, JSON_THROW_ON_ERROR),
        );
    }

    protected function validOffers(): Attribute
    {
        return Attribute::make(
            get: static fn(string $value) => json_decode($value, false, 512, JSON_THROW_ON_ERROR),
        );
    }

    protected function invalidOffers(): Attribute
    {
        return Attribute::make(
            get: static fn(string $value) => json_decode($value, false, 512, JSON_THROW_ON_ERROR),
        );
    }

    protected function bestOffer(): Attribute
    {
        return Attribute::make(
            get: static fn(string $value) => json_decode($value, false, 512, JSON_THROW_ON_ERROR),
        );
    }

}

<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class PSOAppointment extends Model
{
    use HasFactory;
    use Uuids;

    /**
     * @id - guid
     * @run_id - guid - not used yet
     * @input_request - json - the original appointment_request input sent to POST /api/appointment
     * @appointment_request - json - the full payload sent to PSO
     * @appointment_response - json - the full payload returned from PSO
     * @valid_offers - json - any offer with a value > 0
     * @invalid_offers - json - any offer with a value = 0
     * @best_offer - json - the offer with the highest value
     * @summary - string - readable string of valid vs invalid
     * @total_offers_returned - int - total of valid + invalid
     * @total_valid_offers_returned - int - total of valid offers
     * @total_invalid_offers_returned - int - total of invalid offers
     * @status - int - status of the offer; 0 = not responded, 1 = accepted, 2 = declined
     * @accepted_offer_id - int - the ID of the offer that was accepted if any; status would be 1 if this is populated
     * @appointed_check_offer_id - int - the ID of the offer being checked for availability in non-blocking scenarios
     * @accepted_offer - json - the object from valid_offers that represents the accepted offer
     * @activity_id - string - the activity_id being referenced in the Appointment Request (will have an _appt suffix or suffix defined in pso-services.php config file)
     * @base_url - url - the base url of the destination PSO server
     * @input_reference_id - string - the original input_reference_id from @appointment_request
     * @appointed_check_complete - int (bool) - will be 1 if appointed_check_offer_id is populated; used to prevent accept/decline/recheck
     * @appointed_check_result - int (bool) - will be a 1 if true (available), 0 if false (not available)
     * @accept_decline_input_reference_id - string - the input_reference ID of the accept or decline input to PSO
     * @appointed_check_input_reference_id - string the input_reference ID of the appointment check input to PSO
     * @slot_usage_rule_id - string - the slot usage rule used on the original appointment_request  sent to POST /api/appointment
     * @appointment_template_id - string - the appointment template used on the original appointment_request  sent to POST /api/appointment
     * @appointment_template_datetime - datetime - the appointment template datetime used on the original appointment_request  sent to POST /api/appointment
     * @offer_expiry_datetime - datetime - the offer expiry datetime used on the original appointment_request  sent to POST /api/appointment
     * @appointed_check_datetime - datetime - the datetime the appointed check occurred
     * @accept_decline_datetime - datetime - the datetime when accept or decline occurred
     * @accepted_offer_window_start_datetime - datetime - the start window of the accepted offer
     * @appointment_template_duration - string - the value of the optional appointment template duration
     * @user - string - not currently used
     * @dataset_id - string - the dataset ID from the original input
     */

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
            get: fn(string $value) => json_decode($value, false, 512, JSON_THROW_ON_ERROR),
        );
    }

    protected function appointmentRequest(): Attribute
    {
        return Attribute::make(
            get: fn(string $value) => json_decode($value, false, 512, JSON_THROW_ON_ERROR),
        );
    }

    protected function appointmentResponse(): Attribute
    {
        return Attribute::make(
            get: fn(string $value) => json_decode($value, false, 512, JSON_THROW_ON_ERROR),
        );
    }

    protected function validOffers(): Attribute
    {
        return Attribute::make(
            get: fn(string $value) => json_decode($value, false, 512, JSON_THROW_ON_ERROR),
        );
    }

    protected function invalidOffers(): Attribute
    {
        return Attribute::make(
            get: fn(string $value) => json_decode($value, false, 512, JSON_THROW_ON_ERROR),
        );
    }

    protected function bestOffer(): Attribute
    {
        return Attribute::make(
            get: fn(string $value) => json_decode($value, false, 512, JSON_THROW_ON_ERROR),
        );
    }

}

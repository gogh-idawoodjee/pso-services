<?php

namespace App\Models\V2;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @method static Model|static create(array $attributes = [])
 * @method static Builder|static query()
 *
 * @mixin Builder
 */
class PSOAppointment extends Model
{

    use HasUuids;

    protected $table = 'appointment_request';

    protected $fillable = [
        'run_id',
        'short_code',
        'appointment_request',
        'service_api_input',
        'appointment_request_id',
        'status',
        'activity',
        'activity_id',
        'base_url',
        'dataset_id',
        'input_reference_id',
        'input_request',
        'appointment_template_id',
        'slot_usage_rule_id',
        'appointment_template_duration',
        'appointment_template_datetime',
        'offer_expiry_datetime',
        'cleanup_datetime',
        'required_manual_cleanup',
        'appointment_response',
        'valid_offers',
        'invalid_offers',
        'best_offer',
        'summary',
        'total_offers_returned',
        'total_valid_offers_returned',
        'total_invalid_offers_returned',
        'appointed_check_offer_id',
        'appointed_check_complete',
        'appointed_check_result',
        'appointed_check_input_reference_id',
        'appointed_check_datetime',
        'accepted_offer_id',
        'accepted_offer',
        'accept_decline_input_reference_id',
        'accept_decline_datetime',
        'user_id',
    ];

    protected $hidden = [
        'service_api_input',
    ];

    protected $casts = [
        'appointed_check_complete' => 'boolean',
        'appointed_check_result' => 'boolean',
        'accept_decline_datetime' => 'datetime',
        'appointment_template_datetime' => 'datetime',
        'offer_expiry_datetime' => 'datetime',
        'appointed_check_datetime' => 'datetime',
        'input_request' => 'json',
        'activity' => 'json',
        'appointment_request' => 'json',
        'appointment_response' => 'json',
        'valid_offers' => 'json',
        'invalid_offers' => 'json',
        'best_offer' => 'json',
        'service_api_input' => 'json',
    ];

    public function getRouteKeyName(): string
    {
        return 'appointment_request_id';
    }

}

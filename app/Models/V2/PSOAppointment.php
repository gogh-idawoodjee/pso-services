<?php

namespace App\Models\V2;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * 
 *
 * @property string $id
 * @property string|null $run_id
 * @property string $short_code
 * @property string $appointment_request
 * @property string $service_api_input
 * @property string $appointment_request_id
 * @property int $status
 * @property string $activity
 * @property string $activity_id
 * @property string $base_url
 * @property string $dataset_id
 * @property string $input_reference_id
 * @property string $input_request
 * @property string $appointment_template_id
 * @property string|null $slot_usage_rule_id
 * @property string|null $appointment_template_duration
 * @property Carbon|null $appointment_template_datetime
 * @property Carbon $offer_expiry_datetime
 * @property string|null $cleanup_datetime
 * @property int|null $required_manual_cleanup
 * @property string|null $appointment_response
 * @property string|null $valid_offers
 * @property string|null $invalid_offers
 * @property string|null $best_offer
 * @property string|null $summary
 * @property int|null $total_offers_returned
 * @property int|null $total_valid_offers_returned
 * @property int|null $total_invalid_offers_returned
 * @property int|null $appointed_check_offer_id
 * @property bool|null $appointed_check_complete
 * @property bool|null $appointed_check_result
 * @property string|null $appointed_check_input_reference_id
 * @property Carbon|null $appointed_check_datetime
 * @property int|null $accepted_offer_id
 * @property string|null $accepted_offer
 * @property string|null $accept_decline_input_reference_id
 * @property Carbon|null $accept_decline_datetime
 * @property string|null $user_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder<static>|PSOAppointment newModelQuery()
 * @method static Builder<static>|PSOAppointment newQuery()
 * @method static Builder<static>|PSOAppointment query()
 * @method static Builder<static>|PSOAppointment whereAcceptDeclineDatetime($value)
 * @method static Builder<static>|PSOAppointment whereAcceptDeclineInputReferenceId($value)
 * @method static Builder<static>|PSOAppointment whereAcceptedOffer($value)
 * @method static Builder<static>|PSOAppointment whereAcceptedOfferId($value)
 * @method static Builder<static>|PSOAppointment whereActivity($value)
 * @method static Builder<static>|PSOAppointment whereActivityId($value)
 * @method static Builder<static>|PSOAppointment whereAppointedCheckComplete($value)
 * @method static Builder<static>|PSOAppointment whereAppointedCheckDatetime($value)
 * @method static Builder<static>|PSOAppointment whereAppointedCheckInputReferenceId($value)
 * @method static Builder<static>|PSOAppointment whereAppointedCheckOfferId($value)
 * @method static Builder<static>|PSOAppointment whereAppointedCheckResult($value)
 * @method static Builder<static>|PSOAppointment whereAppointmentRequest($value)
 * @method static Builder<static>|PSOAppointment whereAppointmentRequestId($value)
 * @method static Builder<static>|PSOAppointment whereAppointmentResponse($value)
 * @method static Builder<static>|PSOAppointment whereAppointmentTemplateDatetime($value)
 * @method static Builder<static>|PSOAppointment whereAppointmentTemplateDuration($value)
 * @method static Builder<static>|PSOAppointment whereAppointmentTemplateId($value)
 * @method static Builder<static>|PSOAppointment whereBaseUrl($value)
 * @method static Builder<static>|PSOAppointment whereBestOffer($value)
 * @method static Builder<static>|PSOAppointment whereCleanupDatetime($value)
 * @method static Builder<static>|PSOAppointment whereCreatedAt($value)
 * @method static Builder<static>|PSOAppointment whereDatasetId($value)
 * @method static Builder<static>|PSOAppointment whereId($value)
 * @method static Builder<static>|PSOAppointment whereInputReferenceId($value)
 * @method static Builder<static>|PSOAppointment whereInputRequest($value)
 * @method static Builder<static>|PSOAppointment whereInvalidOffers($value)
 * @method static Builder<static>|PSOAppointment whereOfferExpiryDatetime($value)
 * @method static Builder<static>|PSOAppointment whereRequiredManualCleanup($value)
 * @method static Builder<static>|PSOAppointment whereRunId($value)
 * @method static Builder<static>|PSOAppointment whereServiceApiInput($value)
 * @method static Builder<static>|PSOAppointment whereShortCode($value)
 * @method static Builder<static>|PSOAppointment whereSlotUsageRuleId($value)
 * @method static Builder<static>|PSOAppointment whereStatus($value)
 * @method static Builder<static>|PSOAppointment whereSummary($value)
 * @method static Builder<static>|PSOAppointment whereTotalInvalidOffersReturned($value)
 * @method static Builder<static>|PSOAppointment whereTotalOffersReturned($value)
 * @method static Builder<static>|PSOAppointment whereTotalValidOffersReturned($value)
 * @method static Builder<static>|PSOAppointment whereUpdatedAt($value)
 * @method static Builder<static>|PSOAppointment whereUserId($value)
 * @method static Builder<static>|PSOAppointment whereValidOffers($value)
 * @mixin Eloquent
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

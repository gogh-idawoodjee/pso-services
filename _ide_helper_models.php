<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * 
 *
 * @mixin Builder
 * @property string $id
 * @property string|null $run_id
 * @property-read string $input_request
 * @property-read string $appointment_request
 * @property-read string|null $appointment_response
 * @property-read string|null $valid_offers
 * @property-read string|null $invalid_offers
 * @property-read string|null $best_offer
 * @property string|null $summary
 * @property int $total_offers_returned
 * @property int $total_valid_offers_returned
 * @property int|null $total_invalid_offers_returned
 * @property int $status
 * @property int|null $accepted_offer_id
 * @property int|null $appointed_check_offer_id
 * @property string|null $accepted_offer
 * @property string $activity_id
 * @property string $base_url
 * @property string $input_reference_id
 * @property bool|null $appointed_check_complete
 * @property bool|null $appointed_check_result
 * @property string|null $accept_decline_input_reference_id
 * @property string|null $accept_decline_payload
 * @property string|null $appointed_check_input_reference_id
 * @property string|null $slot_usage_rule_id
 * @property string|null $appointed_check_payload
 * @property string|null $book_appointment_payload
 * @property string $appointment_template_id
 * @property \Illuminate\Support\Carbon $appointment_template_datetime
 * @property \Illuminate\Support\Carbon $offer_expiry_datetime
 * @property \Illuminate\Support\Carbon|null $appointed_check_datetime
 * @property \Illuminate\Support\Carbon|null $accept_decline_datetime
 * @property string|null $accepted_offer_window_start_datetime
 * @property string|null $appointment_template_duration
 * @property string|null $user_id
 * @property string|null $dataset_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|PSOAppointment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PSOAppointment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PSOAppointment query()
 * @method static \Illuminate\Database\Eloquent\Builder|PSOAppointment whereAcceptDeclineDatetime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PSOAppointment whereAcceptDeclineInputReferenceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PSOAppointment whereAcceptDeclinePayload($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PSOAppointment whereAcceptedOffer($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PSOAppointment whereAcceptedOfferId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PSOAppointment whereAcceptedOfferWindowStartDatetime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PSOAppointment whereActivityId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PSOAppointment whereAppointedCheckComplete($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PSOAppointment whereAppointedCheckDatetime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PSOAppointment whereAppointedCheckInputReferenceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PSOAppointment whereAppointedCheckOfferId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PSOAppointment whereAppointedCheckPayload($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PSOAppointment whereAppointedCheckResult($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PSOAppointment whereAppointmentRequest($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PSOAppointment whereAppointmentResponse($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PSOAppointment whereAppointmentTemplateDatetime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PSOAppointment whereAppointmentTemplateDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PSOAppointment whereAppointmentTemplateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PSOAppointment whereBaseUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PSOAppointment whereBestOffer($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PSOAppointment whereBookAppointmentPayload($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PSOAppointment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PSOAppointment whereDatasetId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PSOAppointment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PSOAppointment whereInputReferenceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PSOAppointment whereInputRequest($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PSOAppointment whereInvalidOffers($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PSOAppointment whereOfferExpiryDatetime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PSOAppointment whereRunId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PSOAppointment whereSlotUsageRuleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PSOAppointment whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PSOAppointment whereSummary($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PSOAppointment whereTotalInvalidOffersReturned($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PSOAppointment whereTotalOffersReturned($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PSOAppointment whereTotalValidOffersReturned($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PSOAppointment whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PSOAppointment whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PSOAppointment whereValidOffers($value)
 */
	class PSOAppointment extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @mixin Builder
 * @property string $id
 * @property string $input_reference
 * @property string $pso_suggestions
 * @property string $output_payload
 * @property string $pso_response
 * @property string $response_time
 * @property string $transfer_stats
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|PSOCommitLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PSOCommitLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PSOCommitLog query()
 * @method static \Illuminate\Database\Eloquent\Builder|PSOCommitLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PSOCommitLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PSOCommitLog whereInputReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PSOCommitLog whereOutputPayload($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PSOCommitLog wherePsoResponse($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PSOCommitLog wherePsoSuggestions($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PSOCommitLog whereResponseTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PSOCommitLog whereTransferStats($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PSOCommitLog whereUpdatedAt($value)
 */
	class PSOCommitLog extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @mixin Builder
 * @property string $id
 * @property string|null $input_reference
 * @property string|null $input_payload
 * @property string|null $output_payload
 * @property string|null $pso_response
 * @property string|null $response_time
 * @property string|null $transfer_stats
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|PSOTravelLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PSOTravelLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PSOTravelLog query()
 * @method static \Illuminate\Database\Eloquent\Builder|PSOTravelLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PSOTravelLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PSOTravelLog whereInputPayload($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PSOTravelLog whereInputReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PSOTravelLog whereOutputPayload($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PSOTravelLog wherePsoResponse($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PSOTravelLog whereResponseTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PSOTravelLog whereTransferStats($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PSOTravelLog whereUpdatedAt($value)
 */
	class PSOTravelLog extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @mixin Builder
 * @property string $id
 * @property string $pso_environment_id
 * @property string $user_id
 * @property string $rota_id
 * @property string $dataset_id
 * @property string|null $manual_scheduling_shift_id
 * @property string|null $standard_shift_id
 * @property string|null $appointment_sla_type_id
 * @property string|null $activity_sla_type_id
 * @property string|null $appointment_template_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property-read \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\PsoEnvironment $environment
 * @method static \Illuminate\Database\Eloquent\Builder|PsoDataset newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PsoDataset newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PsoDataset query()
 * @method static \Illuminate\Database\Eloquent\Builder|PsoDataset whereActivitySlaTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PsoDataset whereAppointmentSlaTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PsoDataset whereAppointmentTemplateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PsoDataset whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PsoDataset whereDatasetId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PsoDataset whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PsoDataset whereManualSchedulingShiftId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PsoDataset wherePsoEnvironmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PsoDataset whereRotaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PsoDataset whereStandardShiftId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PsoDataset whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PsoDataset whereUserId($value)
 */
	class PsoDataset extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @mixin Builder
 * @property string $id
 * @property string $user_id
 * @property string|null $name
 * @property string $base_url
 * @property string $account_id
 * @property string $username
 * @property string|null $manual_scheduling_shift_id
 * @property string|null $standard_shift_id
 * @property string|null $appointment_sla_type_id
 * @property string|null $activity_sla_type_id
 * @property string|null $appointment_template_id
 * @property string $password
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property-read \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PsoDataset> $datasets
 * @property-read int|null $datasets_count
 * @property-read \App\Models\PsoDataset|null $defaultdataset
 * @property-read \App\Models\PsoToken|null $token
 * @method static \Illuminate\Database\Eloquent\Builder|PsoEnvironment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PsoEnvironment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PsoEnvironment query()
 * @method static \Illuminate\Database\Eloquent\Builder|PsoEnvironment whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PsoEnvironment whereActivitySlaTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PsoEnvironment whereAppointmentSlaTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PsoEnvironment whereAppointmentTemplateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PsoEnvironment whereBaseUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PsoEnvironment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PsoEnvironment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PsoEnvironment whereManualSchedulingShiftId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PsoEnvironment whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PsoEnvironment wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PsoEnvironment whereStandardShiftId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PsoEnvironment whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PsoEnvironment whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PsoEnvironment whereUsername($value)
 */
	class PsoEnvironment extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @mixin Builder
 * @property string $id
 * @property string $name
 * @property string $pso_environment_id
 * @property string $token
 * @property string $token_expiry
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $is_valid_token
 * @method static \Illuminate\Database\Eloquent\Builder|PsoToken newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PsoToken newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PsoToken query()
 * @method static \Illuminate\Database\Eloquent\Builder|PsoToken whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PsoToken whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PsoToken whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PsoToken wherePsoEnvironmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PsoToken whereToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PsoToken whereTokenExpiry($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PsoToken whereUpdatedAt($value)
 */
	class PsoToken extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property-read mixed $is_valid_token
 * @method static \Illuminate\Database\Eloquent\Builder|Token newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Token newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Token query()
 */
	class Token extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @method static \Illuminate\Database\Eloquent\Builder|psorun newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|psorun newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|psorun query()
 */
	class psorun extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @method static \Illuminate\Database\Eloquent\Builder|psorundata newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|psorundata newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|psorundata query()
 */
	class psorundata extends \Eloquent {}
}


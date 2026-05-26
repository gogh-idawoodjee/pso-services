<?php

namespace App\Models\V2;

use App\Enums\TravelLogStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/** @mixin Builder */

// Optional PHPDoc for better IDE support

/**
 * 
 *
 * @property string $id
 * @property string|null $input_reference
 * @property string|null $address_from
 * @property string|null $address_to
 * @property string|null $google_response
 * @property string|null $input_payload
 * @property string|null $output_payload
 * @property TravelLogStatus|null $status
 * @property string|null $pso_response
 * @property string|null $response_time
 * @property string|null $transfer_stats
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read string|null $address_from_text
 * @property-read string|null $address_to_text
 * @property-read string|null $distance_in_km
 * @property-read string|null $google_distance
 * @property-read string|null $google_duration
 * @property-read string|null $pso_distance
 * @property-read string|null $pso_time
 * @property-read string|null $pso_time_formatted
 * @property-read string|null $travel_detail_request_id
 * @method static Builder<static>|PSOTravelLog newModelQuery()
 * @method static Builder<static>|PSOTravelLog newQuery()
 * @method static Builder<static>|PSOTravelLog query()
 * @method static Builder<static>|PSOTravelLog whereAddressFrom($value)
 * @method static Builder<static>|PSOTravelLog whereAddressTo($value)
 * @method static Builder<static>|PSOTravelLog whereCreatedAt($value)
 * @method static Builder<static>|PSOTravelLog whereGoogleResponse($value)
 * @method static Builder<static>|PSOTravelLog whereId($value)
 * @method static Builder<static>|PSOTravelLog whereInputPayload($value)
 * @method static Builder<static>|PSOTravelLog whereInputReference($value)
 * @method static Builder<static>|PSOTravelLog whereOutputPayload($value)
 * @method static Builder<static>|PSOTravelLog wherePsoResponse($value)
 * @method static Builder<static>|PSOTravelLog whereResponseTime($value)
 * @method static Builder<static>|PSOTravelLog whereStatus($value)
 * @method static Builder<static>|PSOTravelLog whereTransferStats($value)
 * @method static Builder<static>|PSOTravelLog whereUpdatedAt($value)
 * @mixin Eloquent
 */
class PSOTravelLog extends Model
{
    use HasUuids;


    protected $table = 'psotravellog';

    protected $fillable = [
        'id',
        'input_reference',
        'address_from',
        'address_to',
        'google_response',
        'input_payload',
        'output_payload',
        'status',
        'pso_response',
        'response_time',
        'transfer_stats',
        'warnings',
    ];

    protected $casts = [
        'status' => TravelLogStatus::class,
        'address_from' => 'json',
        'address_to' => 'json',
        'google_response' => 'json',
        'input_payload' => 'json',
        'output_payload' => 'json',
        'pso_response' => 'json',
        'transfer_stats' => 'json',
    ];

    public function getTravelDetailRequestIdAttribute(): string|null
    {
        return data_get($this->pso_response, 'travel_detail_request_id');
    }

    public function getPsoTimeAttribute(): string|null
    {
        return data_get($this->pso_response, 'time');
    }

    public function getPsoTimeFormattedAttribute(): string|null
    {
        $timeString = data_get($this->pso_response, 'time');

        if (!$timeString) {
            return null;
        }

        [$hours, $minutes, $seconds] = array_map('intval', explode(':', $timeString));

        $totalSeconds = ($hours * 3600) + ($minutes * 60) + $seconds;

        $days = intdiv($totalSeconds, 86400);
        $hours = intdiv($totalSeconds % 86400, 3600);
        $minutes = intdiv($totalSeconds % 3600, 60);
        $seconds = $totalSeconds % 60;

        $parts = [];
        if ($days > 0) {
            $parts[] = "{$days} day" . ($days > 1 ? 's' : '');
        }
        if ($hours > 0) {
            $parts[] = "{$hours} hour" . ($hours > 1 ? 's' : '');
        }
        if ($minutes > 0) {
            $parts[] = "{$minutes} minute" . ($minutes > 1 ? 's' : '');
        }
        if ($seconds > 0 || empty($parts)) {
            $parts[] = "{$seconds} second" . ($seconds > 1 ? 's' : '');
        }

        return implode(' ', $parts);
    }

    public function getPsoDistanceAttribute(): string|null
    {
        return data_get($this->pso_response, 'distance');
    }

    public function getDistanceInKmAttribute(): string|null
    {
        $distanceInMetres = data_get($this->pso_response, 'distance');

        if ($distanceInMetres === null) {
            return null;
        }

        return number_format($distanceInMetres / 1000, 2) . ' km';
    }

    public function getGoogleDistanceAttribute(): string|null
    {
        return data_get($this->google_response, 'distance.text');
    }

    public function getGoogleDurationAttribute(): string|null
    {
        return data_get($this->google_response, 'duration.text');
    }

    public function getAddressFromTextAttribute(): string|null
    {
        return data_get($this->address_from, 'address');
    }

    public function getAddressToTextAttribute(): string|null
    {
        return data_get($this->address_to, 'address');
    }


}

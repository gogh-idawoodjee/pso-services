<?php

namespace App\Models\V2;

use App\Enums\TravelLogStatus;
use App\Traits\Uuids;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use JsonException;

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
    use Uuids;


    protected $table = 'psotravellog';
    protected $guarded = [];

    protected $casts = [
        'status' => TravelLogStatus::class,
    ];

    /**
     * @throws JsonException
     */
    public function getTravelDetailRequestIdAttribute(): string|null
    {
        return data_get(json_decode($this->pso_response, true, 512, JSON_THROW_ON_ERROR), 'travel_detail_request_id');
    }

    /**
     * @throws JsonException
     */
    public function getPsoTimeAttribute(): string|null
    {
        return data_get(json_decode($this->pso_response, true, 512, JSON_THROW_ON_ERROR), 'time');
    }

    /**
     * @throws JsonException
     */
    public function getPsoTimeFormattedAttribute(): string|null
    {
        $timeString = data_get(
            json_decode($this->pso_response, true, 512, JSON_THROW_ON_ERROR),
            'time'
        );

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


    /**
     * @throws JsonException
     */
    public function getPsoDistanceAttribute(): string|null
    {
        return data_get(json_decode($this->pso_response, true, 512, JSON_THROW_ON_ERROR), 'distance');
    }

    /**
     * @throws JsonException
     */
    public function getDistanceInKmAttribute(): string|null
    {
        $distanceInMetres = data_get(
            json_decode($this->pso_response, true, 512, JSON_THROW_ON_ERROR),
            'distance'
        );

        if ($distanceInMetres === null) {
            return null;
        }

        return number_format($distanceInMetres / 1000, 2) . ' km';
    }

    /**
     * @throws JsonException
     */
    public function getGoogleDistanceAttribute(): string|null
    {
        $response = json_decode($this->google_response, true, 512, JSON_THROW_ON_ERROR);
        return data_get($response, 'distance.text');
    }

    /**
     * @throws JsonException
     */
    public function getGoogleDurationAttribute(): string|null
    {
        $response = json_decode($this->google_response, true, 512, JSON_THROW_ON_ERROR);
        return data_get($response, 'duration.text');
    }

    /**
     * @throws JsonException
     */
    public function getAddressFromTextAttribute(): string|null
    {
        $data = json_decode($this->address_from, true, 512, JSON_THROW_ON_ERROR);
        return data_get($data, 'address');
    }

    /**
     * @throws JsonException
     */
    public function getAddressToTextAttribute(): string|null
    {
        $data = json_decode($this->address_to, true, 512, JSON_THROW_ON_ERROR);
        return data_get($data, 'address');
    }


}

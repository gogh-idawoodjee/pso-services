<?php

namespace App\Models;

use App\Enums\TravelLogStatus;
use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use JsonException;

/** @mixin Builder */

// Optional PHPDoc for better IDE support

/**
 * @property TravelLogStatus $status
 * @property string|null $travel_detail_request_id
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

    public function getAddressFromTextAttribute(): string|null
    {
        $data = json_decode($this->address_from, true, 512, JSON_THROW_ON_ERROR);
        return data_get($data, 'address');
    }

    public function getAddressToTextAttribute(): string|null
    {
        $data = json_decode($this->address_to, true, 512, JSON_THROW_ON_ERROR);
        return data_get($data, 'address');
    }


}

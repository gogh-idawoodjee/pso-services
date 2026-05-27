<?php

namespace App\Models\V2;

use App\Enums\TravelLogStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static Model|static create(array $attributes = [])
 * @method static Builder|static query()
 *
 * @mixin Builder
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

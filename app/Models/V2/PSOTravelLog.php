<?php

namespace App\Models\V2;

use App\Enums\TravelLogStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
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
        'callback_url',
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

    protected function travelDetailRequestId(): Attribute
    {
        return Attribute::make(
            get: fn (): string|null => data_get($this->pso_response, 'travel_detail_request_id'),
        );
    }

    protected function psoTime(): Attribute
    {
        return Attribute::make(
            get: fn (): string|null => data_get($this->pso_response, 'time'),
        );
    }

    protected function psoTimeFormatted(): Attribute
    {
        return Attribute::make(
            get: function (): string|null {
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
            },
        );
    }

    protected function psoDistance(): Attribute
    {
        return Attribute::make(
            get: fn (): string|null => data_get($this->pso_response, 'distance'),
        );
    }

    protected function distanceInKm(): Attribute
    {
        return Attribute::make(
            get: function (): string|null {
                $distanceInMetres = data_get($this->pso_response, 'distance');

                if ($distanceInMetres === null) {
                    return null;
                }

                return number_format($distanceInMetres / 1000, 2) . ' km';
            },
        );
    }

    protected function googleDistance(): Attribute
    {
        return Attribute::make(
            get: fn (): string|null => data_get($this->google_response, 'distance.text'),
        );
    }

    protected function googleDuration(): Attribute
    {
        return Attribute::make(
            get: fn (): string|null => data_get($this->google_response, 'duration.text'),
        );
    }

    protected function addressFromText(): Attribute
    {
        return Attribute::make(
            get: fn (): string|null => data_get($this->address_from, 'address'),
        );
    }

    protected function addressToText(): Attribute
    {
        return Attribute::make(
            get: fn (): string|null => data_get($this->address_to, 'address'),
        );
    }
}

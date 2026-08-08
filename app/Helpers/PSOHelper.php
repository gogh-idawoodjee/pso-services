<?php

namespace App\Helpers;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PSOHelper
{

    public static function setTimeZone($time_zone, $check_from_source = false, $source_collection = null): string|Stringable|null
    {
        if ($time_zone) {
            $sign = $time_zone < 0 ? '-' : '+';
            return sprintf('%s%02d:00', $sign, abs($time_zone));
        }

        if ($check_from_source && $source_collection?->first()) {
            return Str::of($source_collection->first()['activity_start'])->substr(20, 6);
        }

        return null;
    }


    /**
     * @param $duration //in minutes
     * @return string
     */
    public static function setPSODuration($duration): string
    {
        $duration = (int)$duration;
        $hours = intdiv($duration, 60);
        $minutes = $duration % 60;

        $result = 'PT';
        if ($hours > 0) {
            $result .= $hours . 'H';
        }
        if ($minutes > 0) {
            $result .= $minutes . 'M';
        }

        // If both hours and minutes are zero, return 'PT0M' as a fallback
        if ($result !== 'PT') {
            return $result;
        }
        return 'PT0M';
    }


    public static function setPSODurationDays($duration): string
    {
        return 'P' . $duration . 'D';
    }

    /**
     * @throws ValidationException
     */
    public static function ValidateSendToPSO(Request $request)// replaced with inline validation see BaseFormRequest
    : void
    {
        Validator::make($request->all(), [
            'token' => Rule::requiredIf($request->send_to_pso === true && !$request->username && !$request->password)
        ])->validate();

        Validator::make($request->all(), [
            'username' => Rule::requiredIf($request->send_to_pso === true && !$request->token)
        ])->validate();

        Validator::make($request->all(), [
            'password' => Rule::requiredIf($request->send_to_pso === true && !$request->token)
        ])->validate();
    }

    /**
     * @throws ValidationException
     */
    public static function ValidateCredentials(Request $request): void
    {
        Validator::make($request->all(), [
            'token' => Rule::requiredIf(!$request->username && !$request->password)
        ])->validate();

        Validator::make($request->all(), [
            'username' => Rule::requiredIf(!$request->token)
        ])->validate();

        Validator::make($request->all(), [
            'password' => Rule::requiredIf(!$request->token)
        ])->validate();
    }

    public static function RotaID($dataset_id, $rota_id)
    {
        return $rota_id ?: $dataset_id;
    }

    public static function GetTimeOut()
    {
        if (config('pso-services.debug.debug_mode_on')) {
            return config('pso-services.debug.debug_timeout');
        }
        return config('pso-services.defaults.timeout');
    }

    public static function notAuth(): JsonResponse
    {
        return response()->json([
            'status' => 401,
            'description' => 'did not pass auth'
        ], 401);
    }

    public static function toIso8601(mixed $datetime): string
    {
        if (!$datetime instanceof Carbon) {
            $datetime = Carbon::parse($datetime);
        }

        return $datetime->format('Y-m-d\TH:i:s');
    }

}

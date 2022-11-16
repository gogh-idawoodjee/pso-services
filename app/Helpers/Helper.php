<?php

namespace App\Helpers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class Helper
{

    public static function setTimeZone($time_zone, $check_from_source = false, $source_collection = null)
    {
        $tz = null;
        if ($time_zone) {
            $tz = '+' . $time_zone . ':00';
            if ($time_zone < 10 && $time_zone > -10) {
                $tz = $time_zone < 0 ? '-0' . abs($time_zone) . ':00' : '+0' . abs($time_zone) . ':00';
            }
        }
        if (!$time_zone && $check_from_source) {
            $tz = Str::of($source_collection->first()['activity_start'])->substr(20, 6);
        }

        return $tz;
    }

    public static function setPSODuration($duration)
    {
        return 'PT' . intdiv(($duration), 60) . 'H' . (($duration) % 60) . 'M';
    }

    public static function setPSODurationDays($duration)
    {
        return 'P' . $duration . 'D';
    }

    /**
     * @throws ValidationException
     */
    public static function ValidateSendToPSO(Request $request)
    {
        Validator::make($request->all(), [
            'token' => Rule::requiredIf($request->send_to_pso == true && !$request->username && !$request->password)
        ])->validate();

        Validator::make($request->all(), [
            'username' => Rule::requiredIf($request->send_to_pso == true && !$request->token)
        ])->validate();

        Validator::make($request->all(), [
            'password' => Rule::requiredIf($request->send_to_pso == true && !$request->token)
        ])->validate();
    }

    /**
     * @throws ValidationException
     */
    public static function ValidateCredentials(Request $request)
    {
        Validator::make($request->all(), [
            'token' => Rule::requiredIf(!$request->username && !$request->password)
        ])->validate();

        Validator::make($request->all(), [
            'username' => Rule::requiredIf(!$request->token)
        ])->validate();

        Validator::make($request->all(), [
            'password' => Rule::requiredIf( !$request->token)
        ])->validate();
    }


}

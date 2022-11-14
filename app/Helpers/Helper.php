<?php // Code within app\Helpers\Helper.php

namespace App\Helpers;

use App\Services\IFSPSOResourceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

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

    public static function authenticatePSO($pso_service_object, Request $request)
    {
        if (!$pso_service_object->isAuthenticated() && $request->send_to_pso) {
            return response()->json([
                'status' => 401,
                'description' => 'did not pass auth'
            ]);
        }

    }

}

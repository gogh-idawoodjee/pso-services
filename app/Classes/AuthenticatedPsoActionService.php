<?php

namespace App\Classes;


use App\Classes\V2\PSOAuthService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

/**
 * Authenticates with PSO and runs a callback with the resolved auth details.
 *
 * Used by both controllers (via PSOAssistV2 trait) and background jobs
 * (e.g. DeleteTempActivity) that need to obtain a PSO session token
 * before performing an action.
 *
 * Flow:
 *  1. If sendToPso is false or a token already exists → run callback immediately
 *  2. Otherwise → call PSOAuthService to obtain a session token, then run callback
 *
 * The callback receives an array with the resolved 'token' key set.
 */
class AuthenticatedPsoActionService
{
    public function run(array $authDetails, callable $callback): JsonResponse|null
    {
        if (!data_get($authDetails, 'sendToPso') || data_get($authDetails, 'token')) {
            return $callback($authDetails);
        }

        try {
            $response = app(PSOAuthService::class)->getToken($authDetails);

            if ($response->status() === 200) {
                $token = data_get($response->getData(), 'message.SessionToken');

                $authDetails['token'] = $token;

                return $callback($authDetails);
            }

            return $response;

        } catch (Exception $e) {
            Log::error('Auth error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Authentication failed',
                'status' => 500
            ], 500);
        }
    }
}

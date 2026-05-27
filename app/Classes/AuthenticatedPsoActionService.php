<?php

namespace App\Classes;


use App\Classes\V2\PSOAuthService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

/**
 * Ensures a valid PSO session token exists before running the given callback.
 *
 * Skips authentication when sending to PSO is disabled or a token is already present.
 * Used by controllers (via PSOAssistV2 trait) and jobs (e.g. DeleteTempActivity).
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

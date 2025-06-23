<?php

namespace App\Classes;


use App\Classes\V2\PSOAuthService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class AuthenticatedPsoActionService
{
    // extract this method into standalone service as jobs like DeleteTempActivity can't use PSOAssist executeAuthenticatedAction
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

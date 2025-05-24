<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\V2\TokenService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use SensitiveParameter;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class TokenController extends Controller
{

    // todo chances are this controller is not needed since we have a service

    protected TokenService $tokenService;

    public function __construct(#[SensitiveParameter] TokenService $tokenService)
    {
        $this->tokenService = $tokenService;
    }

    /**
     * Fetch and return the token.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ConnectionException
     */
    public function fetchToken(Request $request): JsonResponse
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $token = $this->tokenService->getToken($request->username, $request->password);

        if (!$token) {
            return response()->json(['error' => 'Authentication failed'], ResponseAlias::HTTP_UNAUTHORIZED);
        }

        return response()->json([
            'message' => 'Token retrieved successfully',
            'token' => $token,
        ]);
    }
}

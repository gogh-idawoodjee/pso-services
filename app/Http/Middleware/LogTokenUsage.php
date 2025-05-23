<?php

namespace App\Http\Middleware;

use App\Models\V2\TokenUsageLog;
use Closure;
use Illuminate\Http\Request;

class LogTokenUsage
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $token = $request->user()->currentAccessToken();

        if ($token) {
            TokenUsageLog::create([
                'token_id' => $token->id,
                'ip_address' => $request->ip(),
                'method' => $request->method(),
                'route' => $request->route() ? $request->route()->getName() ?? $request->path() : $request->path(),
                'metadata' => [
                    'user_agent' => $request->userAgent(),
                    'headers' => $request->headers->all(),
                ],
            ]);
        }

        return $response;
    }
}

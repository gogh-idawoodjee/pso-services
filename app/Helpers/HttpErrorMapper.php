<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;
use Throwable;

final class HttpErrorMapper
{
    /**
     * Map a Laravel HTTP ConnectionException (wrapping cURL) to a clean JSON response.
     *
     * - DNS resolution failure (cURL 6)  -> 502 Bad Gateway
     * - Connect/timeout (cURL 7/28)     -> 504 Gateway Timeout
     * - TLS handshake (cURL 35)         -> 502 Bad Gateway
     * - Fallback                         -> 504 Gateway Timeout
     *
     * @param Throwable $e   The caught ConnectionException
     * @param string|null $url Optional upstream URL (helps clients + logs)
     * @param string|null $cid Optional correlation ID for tracing
     */
    public static function fromConnectionException(Throwable $e, string|null $url = null, string|null $cid = null): JsonResponse
    {
        $msg    = $e->getMessage();
        $debug  = (bool) config('app.debug');

        // Defaults
        $status = 504;
        $code   = 'UPSTREAM_TIMEOUT';
        $human  = 'Upstream timed out';

        // Heuristics by cURL error code in message
        if (str_contains($msg, 'cURL error 6')) {
            $status = 502; $code = 'UPSTREAM_DNS_FAILURE';  $human = 'Upstream host (base url) could not be resolved';
        } elseif (str_contains($msg, 'cURL error 7')) {
            $status = 502; $code = 'UPSTREAM_CONNECT_FAILURE'; $human = 'Upstream (base url) connection failed';
        } elseif (str_contains($msg, 'cURL error 28')) {
            $status = 504; $code = 'UPSTREAM_TIMEOUT';      $human = 'Upstream (base url) timed out';
        } elseif (str_contains($msg, 'cURL error 35')) {
            $status = 502; $code = 'UPSTREAM_TLS_FAILURE';  $human = 'Upstream (base url) TLS handshake failed';
        }

        return response()->json([
            'error' => [
                'code'           => $code,
                'http_status'    => $status,
                'message'        => $human,
                'details'        => $debug ? $msg : null,
                'upstream'       => $url,
                'correlation_id' => $cid,
            ],
        ], $status);
    }
}

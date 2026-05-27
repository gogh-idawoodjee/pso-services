<?php

namespace App\Classes\V2;

use App\Traits\V2\ApiResponses;
use Exception;
use Illuminate\Support\Facades\Log;

/**
 * Base class for all V2 services.
 *
 * Services are stateless — PsoClient is injected via the container
 * and per-request data flows through PsoContext method parameters.
 */
abstract class BaseService
{
    use ApiResponses;

    public function __construct(protected PsoClient $psoClient) {}

    public function logError(Exception|string $e, $method, $class): void
    {
        $message = $e instanceof Exception ? $e->getMessage() : $e;
        Log::error("Unexpected error in {$method}. This method is inside {$class}: {$message}");
    }
}

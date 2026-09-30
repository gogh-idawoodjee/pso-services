<?php

namespace App\Http\OpenApi;

/**
 * Shared PHPDoc type strings for the `data` key of V2 response envelopes,
 * consumed by the response attributes in this namespace.
 */
final class PsoTypes
{
    /** PsoClient::sendOrSimulate() once PSO has accepted the payload. */
    public const string SENT_PAYLOAD = 'array{payloadToPso: object, responseFromPso: object}';

    /** PsoClient::sendOrSimulate() / buildPayload(useWrapper: true) on a dry run. */
    public const string DRY_RUN_PAYLOAD = 'array{payloadToPso: object}';
}

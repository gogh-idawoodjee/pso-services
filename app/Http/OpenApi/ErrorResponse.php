<?php

namespace App\Http\OpenApi;

use Attribute;
use Dedoc\Scramble\Attributes\Response;

/**
 * Documents the envelope produced by ApiResponses::error(). See SentToPso.
 */
#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_METHOD)]
class ErrorResponse extends Response
{
    public function __construct(
        int $status,
        string $message,
        ?string $description = null,
    ) {
        parent::__construct(
            status: $status,
            description: $description ?? $message,
            type: "array{message: string, status: {$status}}",
            examples: [['message' => $message, 'status' => $status]],
        );
    }
}

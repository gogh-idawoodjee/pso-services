<?php

namespace App\Http\OpenApi;

use Attribute;
use Dedoc\Scramble\Attributes\Response;

/**
 * Documents the 202 dry-run envelope produced by ApiResponses::notSentToPso()
 * (no session token, or environment.sendToPso=false). See SentToPso.
 */
#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_METHOD)]
class NotSentToPso extends Response
{
    public const string MESSAGE = 'Successful. Not sent to PSO by Request';

    /**
     * @param  string  $data  PHPDoc type of the `data` key
     * @param  array<int, mixed>  $examples  example values for the `data` key
     */
    public function __construct(
        string $data = PsoTypes::DRY_RUN_PAYLOAD,
        array $examples = [],
        ?string $description = 'Dry run: payload built but not sent to PSO',
    ) {
        parent::__construct(
            status: 202,
            description: $description,
            type: "array{data: {$data}, status: 202, message: '".self::MESSAGE."', additionalDetails?: string}",
            examples: array_map(
                static fn (mixed $example) => ['data' => $example, 'status' => 202, 'message' => self::MESSAGE],
                $examples,
            ),
        );
    }
}

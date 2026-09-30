<?php

namespace App\Http\OpenApi;

use Attribute;
use Dedoc\Scramble\Attributes\Response;

/**
 * Documents the 200 envelope produced by ApiResponses::ok(). See SentToPso.
 *
 * Each example is a full envelope minus `status`, since ok() responses carry
 * an optional, endpoint-specific message: ['data' => ..., 'message' => ...].
 */
#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_METHOD)]
class OkResponse extends Response
{
    /**
     * @param  string  $data  PHPDoc type of the `data` key
     * @param  array<int, array{data: mixed, message?: string}>  $examples
     */
    public function __construct(
        string $data = 'mixed',
        array $examples = [],
        ?string $description = 'Success',
    ) {
        parent::__construct(
            status: 200,
            description: $description,
            type: "array{data: {$data}, status: 200, message?: string}",
            examples: array_map(
                static fn (array $example) => ['data' => $example['data'], 'status' => 200] + $example,
                $examples,
            ),
        );
    }
}

<?php

namespace App\Http\OpenApi;

use Attribute;
use Dedoc\Scramble\Attributes\Response;

/**
 * Documents the 200 envelope produced by ApiResponses::sentToPso().
 *
 * Scramble can't infer V2 response bodies (they're built dynamically behind
 * executeAuthenticatedAction()), so each action declares its envelopes with
 * these attributes. Pass only the `data` shape/example; the envelope around
 * it is filled in here.
 */
#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_METHOD)]
class SentToPso extends Response
{
    public const string MESSAGE = 'Successful. Sent to PSO';

    /**
     * @param  string  $data  PHPDoc type of the `data` key
     * @param  array<int, mixed>  $examples  example values for the `data` key
     */
    public function __construct(
        string $data = PsoTypes::SENT_PAYLOAD,
        array $examples = [],
        ?string $description = 'Sent to PSO',
    ) {
        parent::__construct(
            status: 200,
            description: $description,
            type: "array{data: {$data}, status: 200, message: '".self::MESSAGE."', additionalDetails?: string, resultsUrl?: string}",
            examples: array_map(
                static fn (mixed $example) => ['data' => $example, 'status' => 200, 'message' => self::MESSAGE],
                $examples,
            ),
        );
    }
}

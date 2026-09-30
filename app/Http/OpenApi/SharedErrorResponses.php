<?php

namespace App\Http\OpenApi;

use Dedoc\Scramble\Support\Generator\Operation;
use Dedoc\Scramble\Support\Generator\Response;
use Dedoc\Scramble\Support\Generator\Schema;
use Dedoc\Scramble\Support\Generator\Types\IntegerType;
use Dedoc\Scramble\Support\Generator\Types\ObjectType;
use Dedoc\Scramble\Support\Generator\Types\StringType;
use Dedoc\Scramble\Support\Generator\Types\UnknownType;
use Dedoc\Scramble\Support\RouteInfo;

/**
 * Adds the error responses every V2 action can produce, so they don't have to
 * be repeated as attributes on each controller method. A code an action
 * already documents itself (e.g. the 401 on health-check) is left alone.
 *
 *  - 401: PSO rejected the credentials/session (PsoClient::handleErrorResponse)
 *  - 500: unexpected failure caught by a service or AuthenticatedPsoActionService
 *  - 502/504: PSO host unreachable (HttpErrorMapper::fromConnectionException)
 */
class SharedErrorResponses
{
    public function __invoke(Operation $operation, RouteInfo $routeInfo): void
    {
        $documentedCodes = collect($operation->responses)
            ->map(static fn (mixed $response): mixed => $response instanceof Response ? (string) $response->code : null)
            ->filter()
            ->all();

        foreach ($this->responses() as $response) {
            if (! in_array((string) $response->code, $documentedCodes, true)) {
                $operation->addResponse($response);
            }
        }
    }

    /**
     * @return array<int, Response>
     */
    private function responses(): array
    {
        return [
            $this->errorEnvelope(
                401,
                'PSO rejected the credentials or session token',
                ['error' => 'Unauthorized. Please check your session or login credentials.', 'details' => ['Message' => 'AUTHENTICATION_FAILED']],
            ),
            $this->errorEnvelope(
                500,
                'Unexpected error',
                'An unexpected error occurred',
                messageIsString: true,
            ),
            $this->upstreamFailure(502, 'PSO host could not be reached', 'UPSTREAM_CONNECT_FAILURE', 'Upstream (base url) connection failed'),
            $this->upstreamFailure(504, 'PSO host timed out', 'UPSTREAM_TIMEOUT', 'Upstream (base url) timed out'),
        ];
    }

    /**
     * The ApiResponses::error() envelope: { message, status }.
     */
    private function errorEnvelope(int $status, string $description, mixed $exampleMessage, bool $messageIsString = false): Response
    {
        $messageType = $messageIsString
            ? new StringType
            : (new ObjectType)
                ->addProperty('error', new StringType)
                ->addProperty('details', new UnknownType)
                ->setRequired(['error']);

        $type = (new ObjectType)
            ->addProperty('message', $messageType)
            ->addProperty('status', (new IntegerType)->enum([$status]))
            ->setRequired(['message', 'status'])
            ->examples([['message' => $exampleMessage, 'status' => $status]]);

        return Response::make($status)
            ->setDescription($description)
            ->setContent('application/json', Schema::fromType($type));
    }

    /**
     * The HttpErrorMapper envelope: { error: { code, http_status, message, details, upstream, correlation_id } }.
     */
    private function upstreamFailure(int $status, string $description, string $code, string $message): Response
    {
        $error = (new ObjectType)
            ->addProperty('code', new StringType)
            ->addProperty('http_status', (new IntegerType)->enum([$status]))
            ->addProperty('message', new StringType)
            ->addProperty('details', (new StringType)->nullable(true))
            ->addProperty('upstream', (new StringType)->nullable(true))
            ->addProperty('correlation_id', (new StringType)->nullable(true))
            ->setRequired(['code', 'http_status', 'message']);

        $type = (new ObjectType)
            ->addProperty('error', $error)
            ->setRequired(['error'])
            ->examples([[
                'error' => [
                    'code' => $code,
                    'http_status' => $status,
                    'message' => $message,
                    'details' => null,
                    'upstream' => 'https://mycompany-pso-tst.ifs.cloud/IFSSchedulingRESTfulGateway/api/v1/scheduling/data',
                    'correlation_id' => '9f1c6f0e-6b7e-4f53-9d0a-2f6f4c1f7a11',
                ],
            ]]);

        return Response::make($status)
            ->setDescription($description)
            ->setContent('application/json', Schema::fromType($type));
    }
}

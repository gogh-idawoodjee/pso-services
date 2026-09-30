<?php

namespace App\Http\OpenApi;

use App\Http\Requests\Api\V2\BaseGetFormRequest;
use Dedoc\Scramble\Support\Generator\Operation;
use Dedoc\Scramble\Support\Generator\Parameter;
use Dedoc\Scramble\Support\Generator\Schema;
use Dedoc\Scramble\Support\Generator\Types\StringType;
use Dedoc\Scramble\Support\RouteInfo;
use ReflectionNamedType;

/**
 * Documents the parameters that are shared across many V2 actions and so would
 * otherwise be repeated on each one:
 *
 *  - PSO auth/environment headers on actions that take a BaseGetFormRequest
 *    (GET endpoints have no body, so the environment block is sent as headers)
 *  - descriptions and examples for the route parameters
 */
class PsoCommonParameters
{
    /**
     * @var array<string, array{description: string, example: string, required: bool}>
     */
    private const array HEADERS = [
        'datasetId' => ['description' => 'The dataset ID to use in PSO.', 'example' => 'dataset_12345', 'required' => true],
        'baseUrl' => ['description' => 'The base URL for the PSO environment.', 'example' => 'https://mycompany-pso-tst.ifs.cloud', 'required' => true],
        'accountId' => ['description' => 'The account ID for PSO.', 'example' => 'account_001', 'required' => true],
        'token' => ['description' => 'An existing PSO authentication token. Provide either a token, or a username and password.', 'example' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...', 'required' => false],
        'username' => ['description' => 'The username for PSO authentication. Alternative to token.', 'example' => 'john.doe', 'required' => false],
        'password' => ['description' => 'The password for PSO authentication. Alternative to token.', 'example' => 'P@ssw0rd!', 'required' => false],
    ];

    /**
     * @var array<string, array{description: string, example: string}>
     */
    private const array PATH_PARAMETERS = [
        'activityId' => ['description' => 'The ID of the activity.', 'example' => 'ACT-001'],
        'appointmentRequestId' => ['description' => 'The ID of the appointment request, as returned when the appointment was requested.', 'example' => 'APPT-REQ-001'],
        'resourceId' => ['description' => 'The ID of the resource.', 'example' => 'RES-001'],
        'unavailabilityId' => ['description' => 'The ID of the unavailability.', 'example' => 'UNAVAIL-001'],
        'environment' => ['description' => 'The environment to commit against.', 'example' => 'tst'],
        'id' => ['description' => 'The ID of the Travel Analyzer run.', 'example' => '9f1c6f0e-6b7e-4f53-9d0a-2f6f4c1f7a11'],
    ];

    public function __invoke(Operation $operation, RouteInfo $routeInfo): void
    {
        foreach ($operation->parameters as $parameter) {
            if ($parameter instanceof Parameter && $parameter->in === 'path') {
                $this->describePathParameter($parameter);
            }
        }

        if ($this->usesHeaderAuth($routeInfo)) {
            foreach (self::HEADERS as $name => $header) {
                $operation->addParameters([
                    (new Parameter($name, 'header'))
                        ->description($header['description'])
                        ->required($header['required'])
                        ->setSchema(Schema::fromType(new StringType))
                        ->example($header['example']),
                ]);
            }
        }
    }

    private function describePathParameter(Parameter $parameter): void
    {
        $documentation = self::PATH_PARAMETERS[$parameter->name] ?? null;

        if ($documentation === null) {
            return;
        }

        if (($parameter->description ?? '') === '') {
            $parameter->description($documentation['description']);
        }

        $parameter->example($documentation['example']);
    }

    private function usesHeaderAuth(RouteInfo $routeInfo): bool
    {
        $reflection = $routeInfo->reflectionMethod();

        foreach ($reflection?->getParameters() ?? [] as $methodParameter) {
            $type = $methodParameter->getType();

            if ($type instanceof ReflectionNamedType && is_a($type->getName(), BaseGetFormRequest::class, true)) {
                return true;
            }
        }

        return false;
    }
}

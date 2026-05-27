<?php

namespace App\Traits\V2;

use App\Classes\AuthenticatedPsoActionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

trait PSOAssistV2
{
    use ApiResponses;

    protected function executeAuthenticatedAction(Request $request, callable $action): JsonResponse
    {
        $authDetails = $this->getAuthDetails($request);

        return app(AuthenticatedPsoActionService::class)->run(
            $authDetails,
            function (array $auth) use ($request, $action) {
                $request->merge([
                    'environment' => array_merge(
                        (array) $request->input('environment', []),
                        ['token' => data_get($auth, 'token')]
                    ),
                ]);

                return $action($request);
            }
        );
    }

    protected function getAuthDetails(Request $request): array
    {
        $env = data_get($request->all(), 'environment', []);

        $headers = collect($request->headers->all())->mapWithKeys(static fn($values, $key) => [
            strtolower($key) => $values[0] ?? null,
        ]);

        $token = data_get($env, 'token', $headers->get('token'));
        $baseUrl = data_get($env, 'baseUrl', $headers->get('baseurl'));
        $accountId = data_get($env, 'accountId', $headers->get('accountid'));
        $username = data_get($env, 'username', $headers->get('username'));
        $password = data_get($env, 'password', $headers->get('password'));

        $sendToPso = data_get($env, 'sendToPso');

        if ($sendToPso === null && empty($env)) {
            $sendToPso = true;
        }

        return compact('token', 'baseUrl', 'accountId', 'username', 'password', 'sendToPso');
    }
}

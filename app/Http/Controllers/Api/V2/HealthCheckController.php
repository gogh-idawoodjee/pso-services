<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\OpenApi\OkResponse;
use App\Http\Requests\Api\V2\HealthCheckRequest;
use App\Traits\V2\PSOAssistV2;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\Response;
use Illuminate\Http\JsonResponse;

#[Group('System')]
class HealthCheckController extends Controller
{
    use PSOAssistV2;

    /**
     * Health Check.
     *
     * Validates that authentication credentials are valid by attempting to obtain a PSO token.
     */
    #[OkResponse(data: "array{status: 'healthy'}", examples: [['data' => ['status' => 'healthy']]])]
    #[Response(401, 'PSO rejected the credentials', type: 'array{error: string, details: mixed}', examples: [['error' => 'Unauthorized. Please check your session or login credentials.', 'details' => 'Invalid username or password']])]
    public function check(HealthCheckRequest $request): JsonResponse
    {
        return $this->executeAuthenticatedAction($request, function () {
            return $this->ok(['status' => 'healthy']);
        });
    }
}

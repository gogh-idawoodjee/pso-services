<?php

namespace App\Classes\V2;

use Illuminate\Http\JsonResponse;


class SendOrSimulateBuilder
{
    protected array $payload;
    protected array $environmentData;
    protected string|null $sessionToken;
    protected bool|null $requiresRotaUpdate = null;
    protected string|null $rotaUpdateDescription = null;
//    protected string|null $notSentArrayKey = null;
    protected string|null $additionalDetails = null;
    protected bool $addInputReference = false;

    public function __construct(
        protected object $caller // the controller or class using the trait
    )
    {
    }

    public function payload(array $payload): static
    {
        $this->payload = $payload;
        return $this;
    }

    public function environment(array $data): static
    {
        $this->environmentData = $data;
        return $this;
    }

    public function token(string|null $token): static
    {
        $this->sessionToken = $token;
        return $this;
    }

    public function includeInputReference(): static
    {
        $this->addInputReference = true;
        return $this;
    }

    public function requiresRotaUpdate(bool|null $flag = null, string|null $description = null): static
    {
        $flag ??= true;
        $this->requiresRotaUpdate = $flag;
        $this->rotaUpdateDescription = $description;
        return $this;
    }
// no longer required
//    public function notSentKey(string|null $key): static
//    {
//        $this->notSentArrayKey = $key;
//        return $this;
//    }

    public function additionalDetails(string|null $details): static
    {
        $this->additionalDetails = $details;
        return $this;
    }

    /**
     * Executes the call to sendOrSimulate from the trait.
     */
    public function send(): JsonResponse
    {
        return $this->caller->sendOrSimulate(
            $this->payload,
            $this->environmentData,
            $this->sessionToken,
            $this->requiresRotaUpdate,
            $this->rotaUpdateDescription,
//            $this->notSentArrayKey,
            $this->additionalDetails,
            $this->addInputReference
        );
    }
}

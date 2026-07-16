<?php

namespace App\Classes\V2;

use Illuminate\Http\JsonResponse;
use LogicException;


/**
 * Fluent builder for PsoClient::sendOrSimulate().
 *
 * Usage:
 *   $this->psoClient->sendOrSimulateBuilder()
 *       ->payload([...])
 *       ->environment($env)
 *       ->token($token)
 *       ->includeInputReference('description')
 *       ->send();
 */
class SendOrSimulateBuilder
{
    protected array $payload;
    protected array $environmentData;
    protected string|null $sessionToken;
    protected bool|null $requiresRotaUpdate = null;
    protected string|null $rotaUpdateDescription = null;
    protected string|null $additionalDetails = null;
    protected bool $addInputReference = false;
    protected string|null $inputReferenceDescription = null;
    protected string|null $resultsUrl = null;
    protected int $psoApiVersion = 1;

    public function __construct(
        protected PsoClient $caller
    ) {
    }

    public function psoApiVersion(int $version): static
    {
        $this->psoApiVersion = $version;
        return $this;
    }

    public function resultsUrl(string|null $url): static
    {
        $this->resultsUrl = $url;
        return $this;
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

    public function includeInputReference(string|null $description = null): static
    {
        $this->addInputReference = true;
        $this->inputReferenceDescription = $description;
        return $this;
    }


    public function requiresRotaUpdate(bool|null $flag = null, string|null $description = null): static
    {
        $flag ??= true;
        $this->requiresRotaUpdate = $flag;
        $this->rotaUpdateDescription = $description;
        return $this;
    }

    public function additionalDetails(string|null $details): static
    {
        $this->additionalDetails = $details;
        return $this;
    }

    /**
     * Execute the built request via PsoClient::sendOrSimulate().
     */
    public function send(): JsonResponse
    {
        if (!isset($this->payload) || !isset($this->environmentData)) {
            throw new LogicException('SendOrSimulateBuilder::send() requires payload() and environment() to be set first.');
        }

        return $this->caller->executeSendOrSimulate($this);
    }

    /**
     * @internal Used by PsoClient::executeSendOrSimulate() to call the (protected)
     * sendOrSimulate() method with this builder's state.
     */
    public function toSendOrSimulateArgs(): array
    {
        return [
            'payload' => $this->payload,
            'environmentData' => $this->environmentData,
            'sessionToken' => $this->sessionToken,
            'requiresRotaUpdate' => $this->requiresRotaUpdate,
            'rotaUpdateDescription' => $this->rotaUpdateDescription,
            'additionalDetails' => $this->additionalDetails,
            'addInputReference' => $this->addInputReference,
            'inputReferenceDescription' => $this->inputReferenceDescription,
            'resultsUrl' => $this->resultsUrl,
            'psoApiVersion' => $this->psoApiVersion,
        ];
    }
}

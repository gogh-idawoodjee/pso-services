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

    protected ?string $sessionToken;

    protected ?bool $requiresRotaUpdate = null;

    protected ?string $rotaUpdateDescription = null;

    protected ?string $additionalDetails = null;

    protected bool $addInputReference = false;

    protected ?string $inputReferenceDescription = null;

    protected ?string $inputReferenceDatetime = null;

    protected ?string $resultsUrl = null;

    protected int $psoApiVersion = 1;

    protected bool $useModellingSchema = false;

    public function __construct(
        protected PsoClient $caller
    ) {}

    public function psoApiVersion(int $version): static
    {
        $this->psoApiVersion = $version;

        return $this;
    }

    public function resultsUrl(?string $url): static
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

    public function token(?string $token): static
    {
        $this->sessionToken = $token;

        return $this;
    }

    public function includeInputReference(?string $description = null): static
    {
        $this->addInputReference = true;
        $this->inputReferenceDescription = $description;

        return $this;
    }

    /**
     * Reference datetime PSO should treat as "now" for this write. Only applies
     * when includeInputReference() is also used; defaults to the actual current
     * time when omitted.
     */
    public function datetime(?string $datetime): static
    {
        $this->inputReferenceDatetime = $datetime;

        return $this;
    }

    public function requiresRotaUpdate(?bool $flag = null, ?string $description = null): static
    {
        $flag ??= true;
        $this->requiresRotaUpdate = $flag;
        $this->rotaUpdateDescription = $description;

        return $this;
    }

    public function additionalDetails(?string $details): static
    {
        $this->additionalDetails = $details;

        return $this;
    }

    /**
     * Wrap the payload under the DsModelling schema (RAM_* entities) instead
     * of dsScheduleData/ScheduleData. Ignores includeInputReference() since
     * Modelling payloads carry their own RAM_Update header instead of a
     * scheduling Input_Reference — build that into payload() yourself.
     */
    public function modellingSchema(bool $flag = true): static
    {
        $this->useModellingSchema = $flag;

        return $this;
    }

    /**
     * Execute the built request via PsoClient::sendOrSimulate().
     */
    public function send(): JsonResponse
    {
        if (! isset($this->payload) || ! isset($this->environmentData)) {
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
            'inputReferenceDatetime' => $this->inputReferenceDatetime,
            'resultsUrl' => $this->resultsUrl,
            'psoApiVersion' => $this->psoApiVersion,
            'useModellingSchema' => $this->useModellingSchema,
        ];
    }
}

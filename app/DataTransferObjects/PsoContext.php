<?php

namespace App\DataTransferObjects;

use SensitiveParameter;

/**
 * Carries per-request context through service method calls.
 *
 * Replaces the old pattern of storing sessionToken + data as
 * mutable instance state on BaseService. Every service method
 * receives a PsoContext instead.
 */
readonly class PsoContext
{
    public function __construct(
        #[SensitiveParameter] public string|null $token,
        public array $validated,
    ) {}

    /**
     * Build from a validated request inside executeAuthenticatedAction.
     */
    public static function fromRequest(mixed $request): self
    {
        return new self(
            token: $request->input('environment.token'),
            validated: $request->validated(),
        );
    }

    /**
     * The full environment block (baseUrl, datasetId, token, sendToPso, etc.).
     *
     * Body-based requests (BaseFormRequest) validate these nested under 'environment'.
     * Header-based GET requests (BaseGetFormRequest) validate them flat at the top
     * level, since there's nowhere to nest headers. Fall back to the flat shape when
     * there's no 'environment' key so both request styles work through this DTO.
     */
    public function environment(): array
    {
        return array_key_exists('environment', $this->validated)
            ? data_get($this->validated, 'environment', [])
            : $this->validated;
    }

    /**
     * Shortcut to environment.datasetId — the PSO dataset identifier.
     */
    public function datasetId(): string|null
    {
        return data_get($this->environment(), 'datasetId');
    }

    /**
     * Shortcut to environment.baseUrl — the IFS/PSO server base URL.
     */
    public function baseUrl(): string|null
    {
        return data_get($this->environment(), 'baseUrl');
    }

    /**
     * The IFS/PSO JSON format version (1 or 2). Defaults to 1.
     */
    public function psoApiVersion(): int
    {
        return (int) data_get($this->environment(), 'psoApiVersion', 1);
    }

    /**
     * Access a value from the data block (e.g. 'activityId', 'resourceId').
     */
    public function data(string|null $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return data_get($this->validated, 'data', []);
        }

        return data_get($this->validated, "data.{$key}", $default);
    }
}

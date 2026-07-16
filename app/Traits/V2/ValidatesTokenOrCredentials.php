<?php

namespace App\Traits\V2;

use Illuminate\Validation\Validator;

/**
 * Shared "token OR (username AND password)" auth-sufficiency check, used by both
 * BaseFormRequest (body-based auth, values under environment.*) and
 * BaseGetFormRequest (header-based auth, values pulled from headers).
 *
 * The two aren't identical beyond this check: BaseFormRequest only requires auth
 * when sendToPso is true, while BaseGetFormRequest's headers are always required.
 * That's why this takes a $condition callback rather than assuming "always required".
 */
trait ValidatesTokenOrCredentials
{
    /**
     * @param callable(): array{token: mixed, username: mixed, password: mixed} $valueResolver
     * @param (callable(): bool)|null $condition Only runs the check when this returns true (or is omitted)
     */
    protected function requireTokenOrCredentials(Validator $validator, callable $valueResolver, callable|null $condition = null): void
    {
        $validator->after(function (Validator $validator) use ($valueResolver, $condition) {
            if ($condition && !$condition()) {
                return;
            }

            $values = $valueResolver();

            $hasToken = $this->filledString($values['token'] ?? null);
            $hasUsername = $this->filledString($values['username'] ?? null);
            $hasPassword = $this->filledString($values['password'] ?? null);

            if ($hasToken) {
                return;
            }

            if (!$hasUsername && !$hasPassword) {
                $validator->errors()->add('authentication', 'Either a token or both username and password must be provided.');
            } elseif (!$hasUsername) {
                $validator->errors()->add('username', 'Username is required if token is not provided.');
            } elseif (!$hasPassword) {
                $validator->errors()->add('password', 'Password is required if token is not provided.');
            }
        });
    }

    private function filledString(mixed $value): bool
    {
        return trim((string) $value) !== '';
    }
}

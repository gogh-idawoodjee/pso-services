<?php

namespace App\Services\V2;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use SensitiveParameter;

class TokenService
{
    /**
     * Fetch a new token from the external API.
     *
     * @param string $username
     * @param string $password
     * @return string|null
     * @throws ConnectionException
     */
    public function fetchToken(string $username, #[SensitiveParameter] string $password): string|null
    {
        $response = Http::post('https://external-api.com/token', compact('username', 'password'));

        if ($response->failed()) {
            return null;
        }

        return $response->json()['token'];
    }

    /**
     * Get the token, refreshing it if necessary.
     *
     * @param string $username
     * @param string $password
     * @return string|null
     * @throws ConnectionException
     */
    public function getToken(string $username, string $password): string|null
    {
        $token = Cache::get('external-api-token');
        $expiresAt = Cache::get('external-api-token-expiry');

        // If the token is valid, return it
        if ($token && $expiresAt > now()) {
            return $token;
        }

        // If the token is expired or missing, fetch a new one
        return $this->fetchAndStoreToken($username, $password);
    }

    /**
     * Fetch and store the new token.
     *
     * @param string $username
     * @param string $password
     * @return string|null
     * @throws ConnectionException
     */
    public function fetchAndStoreToken(string $username, string $password): string|null
    {
        $token = $this->fetchToken($username, $password);

        if ($token) {
            $expiresAt = now()->addMinutes(60);
            Cache::put('external-api-token', $token, $expiresAt);
            Cache::put('external-api-token-expiry', $expiresAt, $expiresAt);
        }

        return $token;
    }
}

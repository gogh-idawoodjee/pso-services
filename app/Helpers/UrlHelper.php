<?php

namespace App\Helpers;

use InvalidArgumentException;

class UrlHelper
{
    /**
     * Validate and normalize the PSO base URL.
     *
     * - required
     * - absolute HTTPS URL
     * - no path/query (we control paths)
     * - returns without trailing slash
     *
     * @throws InvalidArgumentException
     */
    public static function normalizeBaseUrl(string|null $baseUrl): string
    {
        if (!is_string($baseUrl) || $baseUrl === '') {
            throw new InvalidArgumentException('Missing baseUrl');
        }

        if (!filter_var($baseUrl, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException('Invalid baseUrl (not a valid URL)');
        }

        $parts = parse_url($baseUrl);
        if (!isset($parts['scheme'], $parts['host']) || strtolower($parts['scheme']) !== 'https') {
            throw new InvalidArgumentException('Invalid baseUrl (https required)');
        }

        if (!empty($parts['path']) || !empty($parts['query'])) {
            throw new InvalidArgumentException('Invalid baseUrl (must not include path or query)');
        }

        return rtrim($baseUrl, '/');
    }
}

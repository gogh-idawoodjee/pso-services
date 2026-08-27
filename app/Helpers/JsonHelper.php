<?php

namespace App\Helpers;

use JsonException;

class JsonHelper
{
    /**
     * @throws JsonException
     */
    public static function encode(mixed $value, bool $unescapedSlashes = false): string
    {
        $flags = JSON_THROW_ON_ERROR;

        if ($unescapedSlashes) {
            $flags |= JSON_UNESCAPED_SLASHES;
        }

        return json_encode($value, $flags);
    }
}

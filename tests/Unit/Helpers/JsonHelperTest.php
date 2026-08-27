<?php

use App\Helpers\JsonHelper;

it('encodes with slashes escaped by default', function () {
    expect(JsonHelper::encode(['url' => 'https://example.com']))
        ->toBe('{"url":"https:\/\/example.com"}');
});

it('encodes with slashes unescaped when requested', function () {
    expect(JsonHelper::encode(['url' => 'https://example.com'], true))
        ->toBe('{"url":"https://example.com"}');
});

it('throws a JsonException on unencodable values', function () {
    JsonHelper::encode(NAN);
})->throws(JsonException::class);

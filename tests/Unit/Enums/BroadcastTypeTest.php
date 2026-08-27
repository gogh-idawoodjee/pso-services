<?php

use App\Enums\BroadcastParameterType;
use App\Enums\BroadcastType;

it('requires to_address and smtp_server for EMAIL', function () {
    expect(BroadcastType::EMAIL->requiredParameters())
        ->toBe([BroadcastParameterType::TO_ADDRESS, BroadcastParameterType::SMTP_SERVER]);
});

it('requires file_path for FILE', function () {
    expect(BroadcastType::FILE->requiredParameters())
        ->toBe([BroadcastParameterType::FILE_PATH]);
});

it('requires mediatype and url for REST', function () {
    expect(BroadcastType::REST->requiredParameters())
        ->toBe([BroadcastParameterType::MEDIATYPE, BroadcastParameterType::URL]);
});

it('requires wsid and url for WEBSERVICE', function () {
    expect(BroadcastType::WEBSERVICE->requiredParameters())
        ->toBe([BroadcastParameterType::WSID, BroadcastParameterType::URL]);
});

it('requires url for FTP', function () {
    expect(BroadcastType::FTP->requiredParameters())
        ->toBe([BroadcastParameterType::URL]);
});

it('requires address for WCF', function () {
    expect(BroadcastType::WCF->requiredParameters())
        ->toBe([BroadcastParameterType::ADDRESS]);
});

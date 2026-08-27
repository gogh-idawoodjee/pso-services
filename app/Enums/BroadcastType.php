<?php

namespace App\Enums;

enum BroadcastType: string
{
    case EMAIL = 'EMAIL';
    case FILE = 'FILE';
    case REST = 'REST';
    case WEBSERVICE = 'WEBSERVICE';
    case FTP = 'FTP';
    case WCF = 'WCF';

    /**
     * Parameter names IFS requires by default for this broadcast type.
     *
     * @return BroadcastParameterType[]
     */
    public function requiredParameters(): array
    {
        return match ($this) {
            self::EMAIL => [BroadcastParameterType::TO_ADDRESS, BroadcastParameterType::SMTP_SERVER],
            self::FILE => [BroadcastParameterType::FILE_PATH],
            self::REST => [BroadcastParameterType::MEDIATYPE, BroadcastParameterType::URL],
            self::WEBSERVICE => [BroadcastParameterType::WSID, BroadcastParameterType::URL],
            self::FTP => [BroadcastParameterType::URL],
            self::WCF => [BroadcastParameterType::ADDRESS],
        };
    }
}

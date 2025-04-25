<?php

namespace App\Enums;

enum BroadcastParameterType: string
{
    case MEDIATYPE = 'mediatype';
    case EXTERNAL_REFERENCE = 'external_reference';
    case AUTHENTICATION = 'authentication';
    case WSID = 'wsid';
    case PASSWORD = 'password';
    case USERNAME = 'username';
    case URL = 'url';
    case COMPRESSION = 'compression';
    case AUTH_TOKEN_URL_CERT_THUMBPRINT = 'auth_token_url_cert_thumbprint';
    case AUTH_TOKEN_URL = 'auth_token_url';
    case ETAG = 'etag';
    case HTTPMETHOD = 'httpmethod';
}

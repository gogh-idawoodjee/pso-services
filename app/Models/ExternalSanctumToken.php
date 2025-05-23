<?php

namespace App\Models;

use Laravel\Sanctum\PersonalAccessToken as SanctumToken;

class ExternalSanctumToken extends SanctumToken
{

    protected $table = 'personal_access_tokens';



}

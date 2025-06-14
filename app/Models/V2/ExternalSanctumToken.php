<?php

namespace App\Models\V2;

use Laravel\Sanctum\PersonalAccessToken as SanctumToken;

/**
 * 
 *
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $tokenable
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExternalSanctumToken newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExternalSanctumToken newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExternalSanctumToken query()
 * @mixin \Eloquent
 */
class ExternalSanctumToken extends SanctumToken
{

    protected $table = 'personal_access_tokens';



}

<?php

namespace App\Models;

use Laravel\Sanctum\PersonalAccessToken as SanctumToken;

/**
 * 
 *
 * @property int $id
 * @property string $tokenable_type
 * @property int $tokenable_id
 * @property string $name
 * @property string $token
 * @property array<array-key, mixed>|null $abilities
 * @property \Illuminate\Support\Carbon|null $last_used_at
 * @property \Illuminate\Support\Carbon|null $expires_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $tokenable
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExternalSanctumToken newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExternalSanctumToken newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExternalSanctumToken query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExternalSanctumToken whereAbilities($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExternalSanctumToken whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExternalSanctumToken whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExternalSanctumToken whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExternalSanctumToken whereLastUsedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExternalSanctumToken whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExternalSanctumToken whereToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExternalSanctumToken whereTokenableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExternalSanctumToken whereTokenableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExternalSanctumToken whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ExternalSanctumToken extends SanctumToken
{

    protected $table = 'personal_access_tokens';



}

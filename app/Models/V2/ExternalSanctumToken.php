<?php

namespace App\Models\V2;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\PersonalAccessToken as SanctumToken;

/**
 * Registered via Sanctum::usePersonalAccessTokenModel() in AppServiceProvider so that V2 code
 * (see TokenUsageLog::token()) can relate to it directly instead of Sanctum's own
 * PersonalAccessToken model.
 *
 * @method static Model|static create(array $attributes = [])
 * @method static Builder|static query()
 *
 * @mixin Builder
 */
class ExternalSanctumToken extends SanctumToken
{
    protected $table = 'personal_access_tokens';
}

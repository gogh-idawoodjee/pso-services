<?php

namespace App\Models\V2;

use App\Traits\Uuids;
use Carbon\Carbon;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

/**
 *
 *
 * @property-read bool $is_valid_token
 * @method static Builder<static>|Token newModelQuery()
 * @method static Builder<static>|Token newQuery()
 * @method static Builder<static>|Token query()
 * @mixin Eloquent
 */
class Token extends Model
{
    use Uuids;

    protected $guarded = [];

    protected function tokenExpiry(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => $value->addHours(1),
        );
    }

    protected function isValidToken(): Attribute
    {
        return Attribute::make(
            get: fn () => Carbon::now()->diffInMinutes(Carbon::create($this->attributes['token_expiry'])) > 2,
        );
    }
}

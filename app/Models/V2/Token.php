<?php

namespace App\Models\V2;

use App\Traits\Uuids;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * 
 *
 * @property-read mixed $is_valid_token
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Token newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Token newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Token query()
 * @mixin \Eloquent
 */
class Token extends Model
{
    use Uuids;

    protected $guarded = [];

    public function SetTokenExpiryAttribute($value)
    {
        $this->attributes['token_expiry'] = $value->addHours(1);
    }

    public function getIsValidTokenAttribute()
    {
        return Carbon::now()->diffInMinutes(Carbon::create($this->attributes['token_expiry'])) > 2;
    }
}

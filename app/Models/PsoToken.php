<?php

namespace App\Models;

use App\Traits\Uuids;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/** @mixin Builder */
class PsoToken extends Model
{
    use Uuids;

    protected $guarded = [];

    public function SetTokenExpiryAttribute($value)
    {
        $this->attributes['token_expiry'] = $value->addHours(1);
    }

    public function getIsValidTokenAttribute()
    {
        return Carbon::now()->diffInMinutes(Carbon::create($this->attributes['token_expiry']), false) > 2;
    }
}

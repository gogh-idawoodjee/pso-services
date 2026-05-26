<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * 
 *
 * @property string $id
 * @property string $name
 * @property string $pso_environment_id
 * @property string $token
 * @property string $token_expiry
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $is_valid_token
 * @method static Builder<static>|PsoToken newModelQuery()
 * @method static Builder<static>|PsoToken newQuery()
 * @method static Builder<static>|PsoToken query()
 * @method static Builder<static>|PsoToken whereCreatedAt($value)
 * @method static Builder<static>|PsoToken whereId($value)
 * @method static Builder<static>|PsoToken whereName($value)
 * @method static Builder<static>|PsoToken wherePsoEnvironmentId($value)
 * @method static Builder<static>|PsoToken whereToken($value)
 * @method static Builder<static>|PsoToken whereTokenExpiry($value)
 * @method static Builder<static>|PsoToken whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class PsoToken extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'pso_environment_id',
        'token',
        'token_expiry',
    ];

    protected $casts = [
        'token_expiry' => 'datetime',
    ];
    public function SetTokenExpiryAttribute($value)
    {
        $this->attributes['token_expiry'] = $value->addHours(1);
    }
    public function getIsValidTokenAttribute()
    {
        return Carbon::now()->diffInMinutes(Carbon::create($this->attributes['token_expiry']), false) > 2;
    }
}

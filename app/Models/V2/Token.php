<?php

namespace App\Models\V2;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Carbon\Carbon;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static Model|static create(array $attributes = [])
 * @method static Builder|static query()
 *
 * @mixin Builder
 */
class Token extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'token',
        'token_expiry',
    ];

    protected $casts = [
        'token_expiry' => 'datetime',
    ];

    protected function tokenExpiry(): Attribute
    {
        return Attribute::make(
            set: static fn ($value) => $value->addHours(1),
        );
    }

    protected function isValidToken(): Attribute
    {
        return Attribute::make(
            get: fn () => Carbon::now()->diffInMinutes(Carbon::create($this->attributes['token_expiry'])) > 2,
        );
    }
}

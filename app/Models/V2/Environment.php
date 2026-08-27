<?php

namespace App\Models\V2;

use App\Models\User;
use App\Models\V2\Scopes\UserOwnedModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Override;

/**
 * Uses the external krang_db connection (see config/database.php) rather than the app's
 * default database — this table lives in a separate service's database.
 *
 * @method static Model|static create(array $attributes = [])
 * @method static Builder|static query()
 *
 * @mixin Builder
 */
class Environment extends Model
{
    use HasUuids;

    protected $connection = 'krang_db';

    protected $hidden = [
        'password',
        'commit_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'user_id' => 'integer',
    ];

    protected $fillable = [
        'id',
        'name',
        'slug',
        'account_id',
        'base_url',
        'description',
        'username',
        'password',
        'commit_token',
        'user_id',
    ];

    /**
     * Route-model binding will use slug instead of id
     */
    #[Override]
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * pso-test-tools encrypts this with Laravel's standard Crypt facade
     * (Crypt::encryptString()) before saving — decryption works here as long
     * as both apps' APP_KEY match.
     */
    protected function password(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value ? Crypt::decryptString($value) : null,
            set: fn (?string $value) => $value ? Crypt::encryptString($value) : null,
        );
    }

    #[Override]
    protected static function booted(): void
    {

        static::addGlobalScope(new UserOwnedModel);
        static::creating(static function (self $env) {
            if (empty($env->slug)) {
                $base = Str::slug($env->name);
                $slug = $base;
                $i = 1;
                while (static::where('slug', $slug)->exists()) {
                    $slug = "{$base}-{$i}";
                    $i++;
                }
                $env->slug = $slug;
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

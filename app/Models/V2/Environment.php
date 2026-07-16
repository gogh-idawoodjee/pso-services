<?php

namespace App\Models\V2;
use App\Models\V2\Scopes\UserOwnedModel;
use App\Models\User;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
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
        'user_id',
    ];

    /**
     * Route-model binding will use slug instead of id
     */
    #[Override] public function getRouteKeyName(): string
    {
        return 'slug';
    }

    #[Override] protected static function booted(): void
    {

        static::addGlobalScope(new UserOwnedModel());
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

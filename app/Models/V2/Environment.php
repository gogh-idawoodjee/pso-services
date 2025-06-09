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
 *
 *
 * @property string $id
 * @property string $account_id
 * @property string $base_url
 * @property string|null $description
 * @property string $name
 * @property string $slug
 * @property string $password
 * @property string $username
 * @property int $user_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $user
 * @method static Builder<static>|Environment newModelQuery()
 * @method static Builder<static>|Environment newQuery()
 * @method static Builder<static>|Environment query()
 * @method static Builder<static>|Environment whereAccountId($value)
 * @method static Builder<static>|Environment whereBaseUrl($value)
 * @method static Builder<static>|Environment whereCreatedAt($value)
 * @method static Builder<static>|Environment whereDescription($value)
 * @method static Builder<static>|Environment whereId($value)
 * @method static Builder<static>|Environment whereName($value)
 * @method static Builder<static>|Environment wherePassword($value)
 * @method static Builder<static>|Environment whereSlug($value)
 * @method static Builder<static>|Environment whereUpdatedAt($value)
 * @method static Builder<static>|Environment whereUserId($value)
 * @method static Builder<static>|Environment whereUsername($value)
 * @mixin Eloquent
 */
class Environment extends Model
{
    use HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $connection = 'krang_db';


//    /**
//     * The attributes that should be hidden for serialization.
//     *
//     * @var array
//     */
////    protected $hidden = [
//////        'password',
////    ];

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

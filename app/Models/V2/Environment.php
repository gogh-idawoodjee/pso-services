<?php

namespace App\Models\V2;
use App\Models\V2\Scopes\UserOwnedModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Override;


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

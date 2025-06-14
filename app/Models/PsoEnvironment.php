<?php

namespace App\Models;

use App\Traits\Uuids;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Database\Eloquent\Builder;

/**
 * 
 *
 * @property string $id
 * @property string $user_id
 * @property string|null $name
 * @property string $base_url
 * @property string $account_id
 * @property string $username
 * @property string $manual_scheduling_shift_id
 * @property string $standard_shift_id
 * @property string $password
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PsoDataset> $datasets
 * @property-read int|null $datasets_count
 * @property-read \App\Models\PsoDataset|null $defaultdataset
 * @property-read \App\Models\PsoToken|null $token
 * @method static Builder<static>|PsoEnvironment newModelQuery()
 * @method static Builder<static>|PsoEnvironment newQuery()
 * @method static Builder<static>|PsoEnvironment query()
 * @method static Builder<static>|PsoEnvironment whereAccountId($value)
 * @method static Builder<static>|PsoEnvironment whereBaseUrl($value)
 * @method static Builder<static>|PsoEnvironment whereCreatedAt($value)
 * @method static Builder<static>|PsoEnvironment whereId($value)
 * @method static Builder<static>|PsoEnvironment whereManualSchedulingShiftId($value)
 * @method static Builder<static>|PsoEnvironment whereName($value)
 * @method static Builder<static>|PsoEnvironment wherePassword($value)
 * @method static Builder<static>|PsoEnvironment whereStandardShiftId($value)
 * @method static Builder<static>|PsoEnvironment whereUpdatedAt($value)
 * @method static Builder<static>|PsoEnvironment whereUserId($value)
 * @method static Builder<static>|PsoEnvironment whereUsername($value)
 * @mixin \Eloquent
 */
class PsoEnvironment extends Model
{
    use HasFactory;
    use Uuids;

    protected $guarded = ['password'];
    protected $primaryKey = 'id';

    public function token()
    {
        return $this->hasOne(PsoToken::class);
    }

    public function datasets()
    {
        return $this->hasMany(PsoDataset::class);
    }
    public function defaultdataset()
    {
        return $this->hasOne(PsoDataset::class)->oldest();
    }
    protected function password(): Attribute
    {
        return Attribute::make(
            get: fn($value) => Crypt::decrypt($value),
            set: fn($value) => Crypt::encrypt($value),
        );
    }
    protected function updatedAt(): Attribute
    {
        return Attribute::make(
            get: fn($value) => Carbon::make($value)->diffForHumans(),
        );
    }
}

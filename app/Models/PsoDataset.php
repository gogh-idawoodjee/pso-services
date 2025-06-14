<?php

namespace App\Models;

use App\Traits\Uuids;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * 
 *
 * @property string $id
 * @property string $pso_environment_id
 * @property string $user_id
 * @property string $rota_id
 * @property string $dataset_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\PsoEnvironment|null $environment
 * @method static Builder<static>|PsoDataset newModelQuery()
 * @method static Builder<static>|PsoDataset newQuery()
 * @method static Builder<static>|PsoDataset query()
 * @method static Builder<static>|PsoDataset whereCreatedAt($value)
 * @method static Builder<static>|PsoDataset whereDatasetId($value)
 * @method static Builder<static>|PsoDataset whereId($value)
 * @method static Builder<static>|PsoDataset wherePsoEnvironmentId($value)
 * @method static Builder<static>|PsoDataset whereRotaId($value)
 * @method static Builder<static>|PsoDataset whereUpdatedAt($value)
 * @method static Builder<static>|PsoDataset whereUserId($value)
 * @mixin \Eloquent
 */
class PsoDataset extends Model
{
    use Uuids;
    protected $guarded = [];
    public function environment()
    {
        return $this->belongsTo(PsoEnvironment::class, 'pso_environment_id', 'id');
    }
    protected function updatedAt(): Attribute
    {
        return Attribute::make(
            get: fn($value) => Carbon::make($value)->diffForHumans(),
        );
    }

}

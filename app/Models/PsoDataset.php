<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/** @mixin Builder */
class PsoDataset extends Model
{
    use Uuids;

    protected $guarded = [];

    public function environment()
    {
        return $this->belongsTo(PsoEnvironment::class, 'pso_environment_id', 'id');
    }

}

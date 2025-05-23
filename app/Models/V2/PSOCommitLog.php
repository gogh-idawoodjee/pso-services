<?php

namespace App\Models\V2;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/** @mixin Builder */
class PSOCommitLog extends Model
{
    use HasFactory;
    use Uuids;

    protected $table='psocommitlog';
    protected $guarded = [];
}

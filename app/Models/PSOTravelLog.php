<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/** @mixin Builder */
class PSOTravelLog extends Model
{
    use HasFactory;
    use Uuids;
     

    protected $table = 'psotravellog';
    protected $guarded = [];
}

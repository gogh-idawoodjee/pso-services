<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Database\Eloquent\Builder;

/** @mixin Builder */
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


    protected function password(): Attribute
    {
        return Attribute::make(
            get: fn($value) => Crypt::decrypt($value),
            set: fn($value) => Crypt::encrypt($value),
        );
    }
}

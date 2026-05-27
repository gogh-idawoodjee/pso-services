<?php

namespace App\Models\V2;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static Model|static create(array $attributes = [])
 * @method static Builder|static query()
 *
 * @mixin Builder
 */
class PSOCommitLog extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'psocommitlog';

    protected $fillable = [
        'id',
        'input_reference',
        'pso_suggestions',
        'output_payload',
        'pso_response',
        'response_time',
        'transfer_stats',
    ];

    protected $casts = [
        'pso_suggestions' => 'json',
        'output_payload' => 'json',
        'transfer_stats' => 'json',
    ];
}

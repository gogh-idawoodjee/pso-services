<?php

namespace App\Models\V2;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * 
 *
 * @property string $id
 * @property string|null $input_reference
 * @property string|null $pso_suggestions
 * @property string|null $output_payload
 * @property string|null $pso_response
 * @property string|null $response_time
 * @property string|null $transfer_stats
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder<static>|PSOCommitLog newModelQuery()
 * @method static Builder<static>|PSOCommitLog newQuery()
 * @method static Builder<static>|PSOCommitLog query()
 * @method static Builder<static>|PSOCommitLog whereCreatedAt($value)
 * @method static Builder<static>|PSOCommitLog whereId($value)
 * @method static Builder<static>|PSOCommitLog whereInputReference($value)
 * @method static Builder<static>|PSOCommitLog whereOutputPayload($value)
 * @method static Builder<static>|PSOCommitLog wherePsoResponse($value)
 * @method static Builder<static>|PSOCommitLog wherePsoSuggestions($value)
 * @method static Builder<static>|PSOCommitLog whereResponseTime($value)
 * @method static Builder<static>|PSOCommitLog whereTransferStats($value)
 * @method static Builder<static>|PSOCommitLog whereUpdatedAt($value)
 * @mixin Eloquent
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

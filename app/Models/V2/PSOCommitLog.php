<?php

namespace App\Models\V2;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 
 *
 * @mixin Builder
 * @property string $id
 * @property string $input_reference
 * @property string $pso_suggestions
 * @property string $output_payload
 * @property string $pso_response
 * @property string $response_time
 * @property string $transfer_stats
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
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
 * @mixin \Eloquent
 */
class PSOCommitLog extends Model
{
    use HasFactory;
    use Uuids;

    protected $table='psocommitlog';
    protected $guarded = [];
}

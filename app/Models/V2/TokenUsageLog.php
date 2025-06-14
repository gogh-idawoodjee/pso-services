<?php

namespace App\Models\V2;

use App\Models\ExternalSanctumToken;
use App\Models\User;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * 
 *
 * @property int $id
 * @property int $token_id
 * @property string|null $ip_address
 * @property string $method
 * @property string $route
 * @property array<array-key, mixed>|null $metadata
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read ExternalSanctumToken|null $token
 * @property-read User|null $user
 * @method static Builder<static>|TokenUsageLog newModelQuery()
 * @method static Builder<static>|TokenUsageLog newQuery()
 * @method static Builder<static>|TokenUsageLog query()
 * @method static Builder<static>|TokenUsageLog whereCreatedAt($value)
 * @method static Builder<static>|TokenUsageLog whereId($value)
 * @method static Builder<static>|TokenUsageLog whereIpAddress($value)
 * @method static Builder<static>|TokenUsageLog whereMetadata($value)
 * @method static Builder<static>|TokenUsageLog whereMethod($value)
 * @method static Builder<static>|TokenUsageLog whereRoute($value)
 * @method static Builder<static>|TokenUsageLog whereTokenId($value)
 * @method static Builder<static>|TokenUsageLog whereUpdatedAt($value)
 * @mixin Eloquent
 */
class TokenUsageLog extends Model
{
    protected $connection = 'krang_db';

    protected $fillable = [
        'token_id',
        'user_id',   // Only keep if your table has this column
        'route',
        'method',
        'ip_address',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function token(): BelongsTo
    {
        return $this->belongsTo(ExternalSanctumToken::class, 'token_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

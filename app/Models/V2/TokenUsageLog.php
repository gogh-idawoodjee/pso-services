<?php

namespace App\Models\V2;

use App\Models\ExternalSanctumToken;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 
 *
 * @property int $id
 * @property int $token_id
 * @property string|null $ip_address
 * @property string $method
 * @property string $route
 * @property array<array-key, mixed>|null $metadata
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read ExternalSanctumToken|null $token
 * @property-read User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TokenUsageLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TokenUsageLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TokenUsageLog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TokenUsageLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TokenUsageLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TokenUsageLog whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TokenUsageLog whereMetadata($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TokenUsageLog whereMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TokenUsageLog whereRoute($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TokenUsageLog whereTokenId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TokenUsageLog whereUpdatedAt($value)
 * @mixin \Eloquent
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

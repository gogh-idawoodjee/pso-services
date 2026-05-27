<?php

namespace App\Models\V2;

use App\Models\User;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static Model|static create(array $attributes = [])
 * @method static Builder|static query()
 *
 * @mixin Builder
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
        'token_id' => 'integer',
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

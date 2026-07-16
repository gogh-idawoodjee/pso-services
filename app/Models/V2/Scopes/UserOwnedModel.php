<?php

namespace App\Models\V2\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class UserOwnedModel implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * When there's no authenticated user (queues, console, scheduled jobs), the scope is
     * skipped entirely rather than filtering to `user_id = null` — those contexts are expected
     * to operate across all users' records.
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (auth()->id()) {
            $builder->where('user_id', auth()->id());
        }
    }
}

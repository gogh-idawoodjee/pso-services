<?php

namespace App\Listeners;

use Laravel\Sanctum\Events\TokenAuthenticated;

class UpdateSanctumTokenUsage
{
    public function handle(TokenAuthenticated $event): void
    {
        $event->token->forceFill([
            'last_used_at' => now(),
        ])->save();
    }
}

<?php

namespace App\Providers;

use App\Models\V2\ExternalSanctumToken;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        Sanctum::usePersonalAccessTokenModel(ExternalSanctumToken::class);

        Gate::define('viewApiDocs', static function ($user = null) {
            return true;
        });
    }
}

<?php

namespace App\Providers;

use App\Helpers\ShortCodeGenerator;
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
        $this->app->singleton('shortcode', function () {
            return new ShortCodeGenerator();
        });
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

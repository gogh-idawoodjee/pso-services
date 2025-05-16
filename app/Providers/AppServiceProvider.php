<?php

namespace App\Providers;

use App\Helpers\PSOHelper;
use App\Models\ExternalSanctumToken;
use GoogleMaps\Facade\GoogleMapsFacade;
use Illuminate\Foundation\AliasLoader;
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

        if ($this->app->environment('local')) {
//        $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
//        $this->app->register(TelescopeServiceProvider::class);
        }

        // Register aliases
        $loader = AliasLoader::getInstance();
        $loader->alias('Helper', PSOHelper::class);
        $loader->alias('GoogleMaps', GoogleMapsFacade::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        //
        Sanctum::usePersonalAccessTokenModel(ExternalSanctumToken::class);


    }
}

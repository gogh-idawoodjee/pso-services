<?php

namespace App\Providers;

use App\Http\OpenApi\PsoCommonParameters;
use App\Http\OpenApi\SharedErrorResponses;
use App\Models\V2\ExternalSanctumToken;
use Dedoc\Scramble\Scramble;
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

        Scramble::configure()->withOperationTransformers([
            app(PsoCommonParameters::class),
            app(SharedErrorResponses::class),
        ]);

        Gate::define('viewApiDocs', static function ($user = null) {
            return true;
        });

        // TEMPORARY — opened for prod log debugging on 2026-07-10, no auth check.
        // REVERT after troubleshooting the /api/v2/travelanalyzer 500.
        Gate::define('viewLogViewer', static function ($user = null) {
            return true;
        });
    }
}

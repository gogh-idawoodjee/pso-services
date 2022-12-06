<?php

namespace App\Console;

use App\Services\IFSPSOAssistService;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();

        $schedule->call(function () {
            $rotatodse = new IFSPSOAssistService(
                config('pso-services.debug.base_url'),
                null,
                config('pso-services.debug.username'),
                config('pso-services.debug.password'),
                config('pso-services.debug.account_id'),
                true
            );


            $rotatodse->sendRotaToDSE(
                config('pso-services.debug.dataset_id'),
                config('pso-services.debug.dataset_id'),
                config('pso-services.debug.base_url'),
                null,
                true,
                null,
                null,
                null,
                "Scheduled Rota to DSE for " . config('pso-services.debug.dataset_id') . " dataset"

            );

        })->everyFiveMinutes();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}

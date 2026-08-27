<?php

use App\Services\V1\IFSPSOAssistService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('logs:clear', function () {
    File::delete(File::glob(storage_path('logs/*.log')));
    File::delete(File::glob(base_path('*.log')));
    $this->comment('Logs have been cleared!');
})->describe('Clear log files');

Schedule::call(function () {
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
        'Scheduled Rota to DSE for '.config('pso-services.debug.dataset_id').' dataset'
    );
})->everyFiveMinutes();

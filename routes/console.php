<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('logs:clear', function () {
    File::delete(File::glob(storage_path('logs/*.log')));
    File::delete(File::glob(base_path('*.log')));
    $this->comment('Logs have been cleared!');
})->describe('Clear log files');

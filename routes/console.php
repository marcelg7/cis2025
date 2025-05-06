<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Schedule::command('shortcodes:sync')
    ->hourly()
    ->before(function () {
        Log::info('About to run shortcodes:sync');
    })
    ->after(function () {
        Log::info('Finished running shortcodes:sync');
    })
    ->onFailure(function () {
        Log::error('Failed to run shortcodes:sync');
    });

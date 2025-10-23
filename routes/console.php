<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Schedule::command('activitylog:prune --days=365')->daily();

// Database backups at 2 AM daily
Schedule::command('backup:run')->daily()->at('02:00');

// Clean up old backups daily at 3 AM
Schedule::command('backup:clean')->daily()->at('03:00');
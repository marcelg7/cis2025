<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Schedule::command('activitylog:prune --days=365')->daily();

// Database backups at 2 AM daily
Schedule::command('backup:run')->daily()->at('02:00');

// Clean up old backups daily at 3 AM
Schedule::command('backup:clean')->daily()->at('03:00');

// Notification checks
Schedule::command('notifications:check-pending-contracts')->hourly();
Schedule::command('notifications:check-contract-renewals')->daily()->at('09:00');
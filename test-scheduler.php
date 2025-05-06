<?php

require __DIR__.'/vendor/autoload.php';

$app = require __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$schedule = $app->make(Illuminate\Console\Scheduling\Schedule::class);
$kernel->schedule($schedule);

$schedule->run();

echo "Scheduler executed\n";
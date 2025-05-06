<?php
namespace App\Console;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        \App\Console\Commands\SyncShortcodes::class,
    ];
    
    // No schedule method needed in Laravel 12.x
    
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');
    }
}
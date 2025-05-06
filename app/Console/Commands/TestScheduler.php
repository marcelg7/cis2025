<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TestScheduler extends Command
{
    protected $signature = 'test:scheduler';
    protected $description = 'Test scheduler command';

    public function handle()
    {
        Log::info('Test scheduler command ran at ' . now());
        $this->info('Test scheduler command executed!');
    }
}
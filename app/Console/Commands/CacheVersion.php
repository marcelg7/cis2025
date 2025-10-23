<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CacheVersion extends Command
{
    protected $signature = 'version:cache';
    protected $description = 'Cache the current git version to a file';

    public function handle()
    {
        try {
            // Get version from git
            $tag = trim(shell_exec('git describe --tags --abbrev=0 2>/dev/null') ?? '');
            
            if (empty($tag)) {
                $hash = trim(shell_exec('git rev-parse --short HEAD 2>/dev/null') ?? '');
                $version = !empty($hash) ? "dev-{$hash}" : 'v4.2025.1';
            } else {
                $version = $tag;
            }

            // Write to storage
            $versionFile = storage_path('framework/version.txt');
            file_put_contents($versionFile, $version);

            $this->info("Version cached: {$version}");
            return 0;
        } catch (\Exception $e) {
            $this->error("Failed to cache version: {$e->getMessage()}");
            return 1;
        }
    }
}

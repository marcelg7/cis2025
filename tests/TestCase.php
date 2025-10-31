<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Artisan;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        // Run migrations for SQLite in-memory database
        Artisan::call('migrate', [
            '--database' => 'sqlite',
            '--path' => 'database/migrations',
        ]);
    }
}

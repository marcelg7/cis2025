<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Blade;
use App\View\Composers\ThemeComposer;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Register theme composer for all views
        View::composer('*', ThemeComposer::class);
        
        // Register setting helper Blade directives
        Blade::if('settingEnabled', function ($key) {
            return \App\Helpers\SettingsHelper::enabled($key);
        });
        
        Blade::directive('setting', function ($expression) {
            return "<?php echo \App\Helpers\SettingsHelper::get($expression); ?>";
        });
    }
}
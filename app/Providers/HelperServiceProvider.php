<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use App\Helpers\SettingHelper;

class HelperServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Make setting helper available in views
        Blade::directive('setting', function ($expression) {
            return "<?php echo \App\Helpers\SettingHelper::get($expression); ?>";
        });
        
        Blade::if('settingEnabled', function ($key) {
            return \App\Helpers\SettingsHelper::enabled($key);
        });
    }
}
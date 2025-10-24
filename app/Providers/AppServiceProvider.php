<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use App\View\Composers\ThemeComposer;
use App\Models\Contract;
use App\Policies\ContractPolicy;
use App\Models\BugReport;
use App\Policies\BugReportPolicy;

class AppServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Contract::class => ContractPolicy::class,
        BugReport::class => BugReportPolicy::class,
    ];

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Register policies
        foreach ($this->policies as $model => $policy) {
            Gate::policy($model, $policy);
        }

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
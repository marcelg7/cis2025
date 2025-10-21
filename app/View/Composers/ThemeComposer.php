<?php

namespace App\View\Composers;

use Illuminate\View\View;
use App\Services\ThemeService;

class ThemeComposer
{
    public function compose(View $view)
    {
        $theme = auth()->check() ? (auth()->user()->theme ?? 'default') : 'default';
        $themeCss = ThemeService::getCSSVariables($theme);
        
        $view->with('themeCss', $themeCss);
        $view->with('currentTheme', $theme);
    }
}
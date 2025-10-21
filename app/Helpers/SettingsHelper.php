<?php
namespace App\Helpers;

use App\Models\Setting;

class SettingsHelper
{
    public static function get($key, $default = null)
    {
        $setting = Setting::where('key', $key)->first();
        
        // ADD THIS DEBUG
        \Log::info('SettingsHelper::get', [
            'key' => $key,
            'setting_found' => $setting ? 'yes' : 'no',
            'value' => $setting ? $setting->value : 'null',
            'returning' => $setting ? $setting->value : $default
        ]);
        
        return $setting ? $setting->value : $default;
    }

    public static function set($key, $value)
    {
        return Setting::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }
}
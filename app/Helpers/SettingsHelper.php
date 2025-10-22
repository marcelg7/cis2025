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

    public static function enabled($key, $default = false)
    {
        $value = self::get($key, $default);

        // Convert string values to boolean
        if (is_string($value)) {
            return in_array(strtolower($value), ['1', 'true', 'yes', 'on'], true);
        }

        return (bool) $value;
    }

    public static function set($key, $value)
    {
        return Setting::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }
}
<?php

use Illuminate\Support\Facades\Auth;


if (!function_exists('get_user_component_style')) {
    function get_user_component_style($component, $property, $default)
    {
        $user = Auth::user();
        $value = $user && isset($user->component_styles[$component][$property]) 
            ? $user->component_styles[$component][$property] 
            : $default;
        \Log::debug('get_user_component_style', [
            'component' => $component,
            'property' => $property,
            'value' => $value,
            'user_id' => $user ? $user->id : null,
        ]);
        return $value;
    }
}
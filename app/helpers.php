<?php

use Illuminate\Support\Facades\Auth;


if (!function_exists('get_user_component_style')) {
    function get_user_component_style($component, $property, $default)
    {
        $user = Auth::user();
        return $user && isset($user->component_styles[$component][$property]) 
            ? $user->component_styles[$component][$property] 
            : $default;
    }
}
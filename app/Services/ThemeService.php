<?php

namespace App\Services;

class ThemeService
{
    protected static $themes = [
        'default' => [
            'primary' => '#4f46e5',
            'primary-hover' => '#4338ca',
            'primary-light' => '#eef2ff',
            'secondary' => '#6b7280',
            'secondary-hover' => '#4b5563',
            'accent' => '#6366f1',
            'background' => '#ffffff',
            'surface' => '#f9fafb',
            'text' => '#111827',
            'text-secondary' => '#6b7280',
            'border' => '#e5e7eb',
            'success' => '#10b981',
            'warning' => '#f59e0b',
            'danger' => '#ef4444',
            'info' => '#3b82f6',
        ],
        'dark' => [
            'primary' => '#6366f1',
            'primary-hover' => '#4f46e5',
            'primary-light' => '#1e1b4b',
            'secondary' => '#9ca3af',
            'secondary-hover' => '#6b7280',
            'accent' => '#818cf8',
            'background' => '#1f2937',
            'surface' => '#111827',
            'text' => '#f9fafb',
            'text-secondary' => '#d1d5db',
            'border' => '#374151',
            'success' => '#10b981',
            'warning' => '#f59e0b',
            'danger' => '#ef4444',
            'info' => '#3b82f6',
        ],
        'high-contrast' => [
            'primary' => '#000000',
            'primary-hover' => '#1f2937',
            'primary-light' => '#f3f4f6',
            'secondary' => '#374151',
            'secondary-hover' => '#1f2937',
            'accent' => '#ffff00',
            'background' => '#ffffff',
            'surface' => '#f9fafb',
            'text' => '#000000',
            'text-secondary' => '#1f2937',
            'border' => '#000000',
            'success' => '#166534',
            'warning' => '#854d0e',
            'danger' => '#991b1b',
            'info' => '#1e40af',
        ],
        'warm' => [
            'primary' => '#f59e0b',
            'primary-hover' => '#d97706',
            'primary-light' => '#fef3c7',
            'secondary' => '#78716c',
            'secondary-hover' => '#57534e',
            'accent' => '#fbbf24',
            'background' => '#fffbeb',
            'surface' => '#fef3c7',
            'text' => '#78350f',
            'text-secondary' => '#92400e',
            'border' => '#fde68a',
            'success' => '#10b981',
            'warning' => '#f59e0b',
            'danger' => '#dc2626',
            'info' => '#0891b2',
        ],
        'cool' => [
            'primary' => '#0891b2',
            'primary-hover' => '#0e7490',
            'primary-light' => '#cffafe',
            'secondary' => '#64748b',
            'secondary-hover' => '#475569',
            'accent' => '#06b6d4',
            'background' => '#f0fdfa',
            'surface' => '#ccfbf1',
            'text' => '#134e4a',
            'text-secondary' => '#115e59',
            'border' => '#99f6e4',
            'success' => '#10b981',
            'warning' => '#f59e0b',
            'danger' => '#ef4444',
            'info' => '#0891b2',
        ],
        'deuteranopia' => [
            'primary' => '#0369a1',
            'primary-hover' => '#075985',
            'primary-light' => '#e0f2fe',
            'secondary' => '#64748b',
            'secondary-hover' => '#475569',
            'accent' => '#f59e0b',
            'background' => '#ffffff',
            'surface' => '#fef3c7',
            'text' => '#0c4a6e',
            'text-secondary' => '#475569',
            'border' => '#bae6fd',
            'success' => '#0891b2',
            'warning' => '#f59e0b',
            'danger' => '#c2410c',
            'info' => '#0369a1',
        ],
        'protanopia' => [
            'primary' => '#7c3aed',
            'primary-hover' => '#6d28d9',
            'primary-light' => '#ede9fe',
            'secondary' => '#64748b',
            'secondary-hover' => '#475569',
            'accent' => '#f59e0b',
            'background' => '#ffffff',
            'surface' => '#fef3c7',
            'text' => '#5b21b6',
            'text-secondary' => '#475569',
            'border' => '#ddd6fe',
            'success' => '#8b5cf6',
            'warning' => '#f59e0b',
            'danger' => '#c2410c',
            'info' => '#7c3aed',
        ],
    ];

    public static function getCSSVariables($theme = 'default'): string
    {
        $colors = self::$themes[$theme] ?? self::$themes['default'];
        
        $css = ':root {' . PHP_EOL;
        foreach ($colors as $name => $value) {
            $css .= "    --color-{$name}: {$value};" . PHP_EOL;
        }
        $css .= '}';
        
        return $css;
    }

    public static function getThemeColors($theme = 'default'): array
    {
        return self::$themes[$theme] ?? self::$themes['default'];
    }
}
<?php

/**
 * Get the application version from cached file or git tag
 *
 * @return string
 */
if (!function_exists('app_version')) {
    function app_version(): string
    {
        try {
            // Try to read from cached version file (production)
            $versionFile = storage_path('framework/version.txt');

            if (file_exists($versionFile)) {
                $version = trim(file_get_contents($versionFile));
                if (!empty($version)) {
                    return $version;
                }
            }

            // Fallback: Try to get the latest git tag (development)
            $tag = trim(shell_exec('git describe --tags --abbrev=0 2>/dev/null') ?? '');

            if (!empty($tag)) {
                return $tag;
            }

            // Fallback: short commit hash
            $hash = trim(shell_exec('git rev-parse --short HEAD 2>/dev/null') ?? '');

            if (!empty($hash)) {
                return 'dev-' . $hash;
            }

            // Final fallback
            return 'v4.2025.1';
        } catch (\Exception $e) {
            return 'v4.2025.1';
        }
    }
}

/**
 * Get the full version with commit info
 *
 * @return string
 */
if (!function_exists('app_version_full')) {
    function app_version_full(): string
    {
        try {
            // Get tag and commit hash
            $tag = trim(shell_exec('git describe --tags --abbrev=0 2>/dev/null') ?? '');
            $hash = trim(shell_exec('git rev-parse --short HEAD 2>/dev/null') ?? '');
            $date = trim(shell_exec('git log -1 --format=%cd --date=short 2>/dev/null') ?? '');

            $parts = [];

            if (!empty($tag)) {
                $parts[] = $tag;
            }

            if (!empty($hash)) {
                $parts[] = "({$hash})";
            }

            if (!empty($date)) {
                $parts[] = $date;
            }

            return !empty($parts) ? implode(' ', $parts) : 'v1.0.0';
        } catch (\Exception $e) {
            return 'v1.0.0';
        }
    }
}

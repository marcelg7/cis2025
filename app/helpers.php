<?php

/**
 * Get the application version from git tag or commit hash
 *
 * @return string
 */
if (!function_exists('app_version')) {
    function app_version(): string
    {
        try {
            // Try to get the latest git tag
            $tag = trim(shell_exec('git describe --tags --abbrev=0 2>/dev/null') ?? '');

            if (!empty($tag)) {
                return $tag;
            }

            // Fallback to short commit hash
            $hash = trim(shell_exec('git rev-parse --short HEAD 2>/dev/null') ?? '');

            if (!empty($hash)) {
                return 'dev-' . $hash;
            }

            // Final fallback
            return 'v1.0.0';
        } catch (\Exception $e) {
            return 'v1.0.0';
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
